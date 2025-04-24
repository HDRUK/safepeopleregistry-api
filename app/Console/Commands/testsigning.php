<?php

namespace App\Console\Commands;

use Http;
use App\Models\Custodian;
use App\Models\Registry;
use Illuminate\Console\Command;

class testsigning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:testsigning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $custodian = Custodian::where('id', 1)->first();
        $ident = Registry::where('id', 1)->first()->digi_ident;
        $payload = [
            'custodian_identifier' => '$2y$12$R8fsAq7./uEUEcU7o7QnQOg8q6bfFBrMZgMWb1qPflPiiaobz41TO',
            'digital_identifier' => '$2y$12$TvwBLJYTkv70bMCSsg6DMu.qluV23TvJVv8tUvbKco3ftvMY0a5Ai'
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = base64_encode(hash_hmac('sha256', $payloadJson, $custodian->unique_identifier, true));

        dd($custodian->client_id . ' and ' . $signature . ' and ' . $ident);

        // $response = Http::withHeaders([
        //     'x-client-id' => $custodian->client_id,
        //     'x-signature' => $signature,
        //     'Content-Type' => 'application/json',
        //     'Accept' => 'application/json',
        // ])->post('http://localhost:8100/api/v1/users/validate', $payload);

        // // dd($custodian);
        // // dd($response->json()['data']['digital_identifier']);
        // $payload = [
        //     'ident' => $response->json()['data']['digital_identifier'],
        // ];

        // // dd($payload);

        // $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
        // $signature = base64_encode(hash_hmac('sha256', $payloadJson, $custodian->unique_identifier, true));
    }
}
