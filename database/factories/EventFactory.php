<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1,10), // A random user id between 1 and 10
            'title' => $this->faker->sentence(4), // A random title with 4 words
            'description' => $this->faker->paragraph, // A random paragraph for the description
            'start_time' => $this->faker->dateTimeBetween('now', '+1 month'), // A random start time between now and 1 month ahead
            'end_time' => $this->faker->dateTimeBetween('+1 month', '+2 months'), // A random end time between 1 and 2 months ahead
            'location' => $this->faker->city, // A random city name for the event location
        ];
    }
}
