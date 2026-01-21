<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class RetryJobsController extends Controller
{
    public function retryAll()
    {
        $failedCount = DB::table('failed_jobs')->count();
        
        if ($failedCount === 0) {
            return response()->json([
                'success' => true,
                'message' => 'No failed jobs to retry',
                'count' => 0
            ]);
        }

        // Run the command
        Artisan::call('jobs:retry', ['--all' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'All failed jobs have been queued for retry',
            'count' => $failedCount
        ]);
    }

    public function retryByUuid(Request $request, $uuid)
    {
        $job = DB::table('failed_jobs')
            ->where('uuid', $uuid)
            ->first();
        
        if (is_null($job)) {
            return response()->json([
                'success' => false,
                'message' => "Failed job with UUID {$uuid} not found"
            ], 404);
        }

        // Run the command with UUID
        Artisan::call('jobs:retry', ['uuid' => $uuid]);
        
        return response()->json([
            'success' => true,
            'message' => "Job {$uuid} has been queued for retry",
            'job' => [
                'uuid' => $job->uuid,
                'queue' => $job->queue,
                'failed_at' => $job->failed_at
            ]
        ]);
    }

    public function listFailed(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        $jobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'jobs' => $jobs
        ]);
    }
}
