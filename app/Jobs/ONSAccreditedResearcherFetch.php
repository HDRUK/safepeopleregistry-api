<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        $this->baseUrlTemplate = env('ONS_ACCREDITED_RESEARCHER_LIST_URL');
        $this->onsAccreditedResearcherListUrl = env('ONS_ACCREDITED_RESEARCHER_LIST_PAGE_URL');
    }

    public function handle(): void
    {
        $response = Http::get($this->onsAccreditedResearcherListUrl);
        if ($response->status() >= 200 && $response->status() < 300) {
            $htmlContent = $response->body();

            $this->processPageContent($htmlContent);
        }

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

            dd($url);
        }

        dd('not found');
    }
}
