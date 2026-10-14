<?php

namespace Tests\Feature;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class UserSroActivityLogTest extends TestCase
{
    public function test_changing_is_sro_is_recorded_in_the_activity_log(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'user_group' => User::GROUP_ORGANISATIONS,
            'organisation_id' => Organisation::factory()->create()->id,
            'is_sro' => 0,
        ]);

        // LogsActivity listens for model events, which the base TestCase turns off.
        $this->withTemporaryObservers(function () use ($user) {
            $user->is_sro = true;
            $user->save();
        });

        $activity = Activity::query()
            ->where('log_name', 'user')
            ->where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertFalse($activity->properties['old']['is_sro']);
        $this->assertTrue($activity->properties['attributes']['is_sro']);
    }
}
