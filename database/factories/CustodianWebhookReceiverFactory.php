<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CustodianWebhookReceiver;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustodianWebhookReceiver>
 */
class CustodianWebhookReceiverFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustodianWebhookReceiver::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'custodian_id' => 1,
            'url' => $this->faker->url,
            'webhook_event' => 1,
        ];
    }
}
