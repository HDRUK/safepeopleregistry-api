<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\UksaLiveFeed;
use Illuminate\Support\Facades\Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ONSAccreditedResearcherFetch implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $baseUrlTemplate = '';
    private $onsAccreditedResearcherListUrl = '';

    public function __construct()
    {
        $this->baseUrlTemplate = config('speedi.system.ons_acredited_researcher_list_url');
        $this->onsAccreditedResearcherListUrl = config('speedi.system.ons_accredited_researcher_list_page_url');
    }

    public function handle(): void
    {
        $response = Http::get($this->onsAccreditedResearcherListUrl);
        if ($response->status() >= 200 && $response->status() < 300) {
            $htmlContent = $response->body();

            $this->processPageContent($htmlContent);
        }

        $response->close();

        // non-200 response returned. Ignore.
        return;
    }

    private function processPageContent(string $page): void
    {
        if (preg_match('/The list of accredited researchers is correct as of (\d{2} \w+ \d{4})/', $page, $matches)) {
            $dateString = $matches[1];
            $date = Carbon::createFromFormat('d F Y', $dateString);
            $year = $date->format('Y');
            $month = $date->format('m');
            $day = $date->format('d');

            $url = sprintf(
                $this->baseUrlTemplate,
                $year,
                $month,
                $day,
                $month,
                $year
            );

            $filePath = storage_path('app/temp.xlsx');
            $response = Http::withOptions(['sink' => $filePath])->get($url);

            if (!$response->successful()) {
                $response->close();
                echo 'unable to download ' . $url . ' file!';
                return;
            }

            $response->close();

            $data = $this->parseONSFile($filePath);
            echo(count($data) . ' found in file...' . "\n");

            $this->writeResearchers($data);
        }
    }

    private function parseONSFile(string $path): array
    {
        $researchers = [];

        $dataTemplate = [
            'last_name' => '',
            'first_name' => '',
            'organisation_name' => '',
            'accreditation_number' => '',
            'accreditation_type' => '',
            'expiry_date' => '',
            'public_record' => '',
            'stage' => '',
        ];

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $spreadsheet->setActiveSheetIndexByName('Report'); // to nudge the active sheet away from intro page

        $worksheet = $spreadsheet->getActiveSheet();

        $maxRow = $worksheet->getHighestRow();
        $maxCol = $worksheet->getHighestColumn();

        $highestColumnIndex = Coordinate::columnIndexFromString($maxCol);

        // Get header row
        $headers = [];
        $colStartIndex = config('speedi.system.ons_column_start_index', 1);
        $rowStartIndex = config('speedi.system.ons_row_start_index', 1);

        for ($col = $colStartIndex; $col <= $highestColumnIndex; $col++) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $headers[$col] = strtolower(str_replace(' ', '_', trim((string) $worksheet->getCell($columnLetter . $rowStartIndex)->getValue())));
        }

        for ($row = $rowStartIndex + 1; $row <= $maxRow; $row++) {
            $rowData = [];

            for ($col = $colStartIndex; $col <= $highestColumnIndex; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $header = $headers[$col] ?? "Column$col";
                $rowData[$header] = trim($worksheet->getCell($columnLetter . $row)->getValue());
            }

            $researchers[] = $rowData;
        }

        return $researchers;
    }

    private function writeResearchers(array $data): void
    {
        foreach ($data as $d) {
            UksaLiveFeed::updateOrCreate($d);
        }
    }
}
