<?php

namespace Database\Factories;

use App\Enums\ContentPillar;
use App\Enums\ContentType;
use App\Enums\DataStatus;
use App\Enums\EvidenceStatus;
use App\Models\ContentLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentLog>
 */
class ContentLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $postDate = fake()->dateTimeBetween('-6 months', '-35 days');

        return [
            'post_date' => $postDate->format('Y-m-d'),
            'platform' => 'Instagram',
            'content_type' => fake()->randomElement(ContentType::cases()),
            'title' => fake()->sentence(4),
            'pillar' => fake()->randomElement(ContentPillar::cases()),
            'pic_name' => fake()->name(),
            'evidence_url' => 'https://www.instagram.com/p/'.fake()->bothify('??????###').'/',
            'evidence_status' => EvidenceStatus::Ada,
            'h30_due_date' => null,
            'data_status' => DataStatus::Completed,
            'views_h30' => fake()->numberBetween(1000, 25000),
            'viewers_h30' => fake()->numberBetween(500, 15000),
            'likes' => fake()->numberBetween(20, 500),
            'comments' => fake()->numberBetween(0, 30),
            'shares_reposts' => fake()->numberBetween(0, 50),
            'saves' => fake()->numberBetween(0, 40),
            'follows' => fake()->numberBetween(0, 25),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * State untuk konten yang masih menunggu evaluasi H+30.
     */
    public function pendingH30(): static
    {
        return $this->state(fn (array $attributes) => [
            'post_date' => now()->subDays(5)->format('Y-m-d'),
            'data_status' => DataStatus::PendingH30,
            'views_h30' => 0,
            'viewers_h30' => 0,
            'likes' => 0,
            'comments' => 0,
            'shares_reposts' => 0,
            'saves' => 0,
            'follows' => 0,
        ]);
    }

    /**
     * State untuk konten tanpa URL evidence.
     */
    public function withoutEvidence(): static
    {
        return $this->state(fn (array $attributes) => [
            'evidence_url' => null,
            'evidence_status' => EvidenceStatus::Belum,
        ]);
    }
}
