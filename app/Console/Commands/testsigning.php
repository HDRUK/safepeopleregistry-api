<?php

namespace App\Console\Commands;

use App\Models\Custodian;
use Illuminate\Console\Command;
use App\Http\Traits\HmacSigning;

class testsigning extends Command
{
    use HmacSigning;
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
        $payload = [
            'email' => 'dan.ackroyd@ghostbusters.com',
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = base64_encode(hash_hmac('sha256', $payloadJson, $custodian->unique_identifier, true));
        dd($signature);
    }
}
