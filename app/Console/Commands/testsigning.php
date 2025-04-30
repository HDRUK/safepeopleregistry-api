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
        // $custodian = Custodian::where('id', 1)->first();
        // $ident = Registry::where('id', 1)->first()->digi_ident;
        // $payload = [
        //     'digital_identifier' => $ident,
        //     // 'email' => 'dan.ackroyd@ghostbusters.com',
        // ];

        $payloadJson = json_encode('{"status": "success", "verification": {"id": "430332bd-ce69-4556-8d98-b8a27e3a45d0", "code": 9001, "person": {"gender": null, "idNumber": "001-1505561-1", "lastName": "Xander [EXAMPLE]", "firstName": "Nican Onio", "citizenship": null, "dateOfBirth": "1989-05-02", "nationality": null, "yearOfBirth": null, "placeOfBirth": null, "pepSanctionMatch": null}, "reason": null, "status": "approved", "comments": [], "document": {"type": "PASSPORT", "state": null, "number": "VL0199336", "country": "DO", "validFrom": null, "validUntil": "2022-03-11"}, "attemptId": "f8815b5c-3c20-469f-ab12-8ba4b5ce49d6", "endUserId": null, "reasonCode": null, "vendorData": null, "decisionTime": "2025-03-25T07:33:34.318335Z", "acceptanceTime": "2025-03-25T07:33:06.958607Z", "additionalVerifiedData": {}}, "technicalData": {"ip": null}}');
        $signature = hash_hmac('sha256', $payloadJson, env('IDVT_SUPPLIER_SECRET_KEY'));
        dd(strtolower($signature));

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
