<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'document_number' => 'IDX-' . $this->faker->unique()->randomNumber(6, true),
            'title' => Str::title($this->faker->words(rand(5, 10), true)),
            'abstract' => $this->faker->paragraphs(rand(2, 4), true),
            'keywords' => implode(', ', $this->faker->words(3)), // Tambahan keywords
            'document_type' => $this->faker->randomElement(['Book', 'Journal Article', 'Conference Paper', 'Report']),
            'pub_year' => $this->faker->numberBetween(2010, 2026),
            'pages' => $this->faker->numberBetween(5, 50),
            'reference_count' => $this->faker->numberBetween(10, 100),
            
            'is_verified' => true, 
            'verification_token' => Str::random(40),
            'doi' => $this->faker->optional(0.7)->passthrough('https://doi.org/10.' . rand(1000, 9999) . '/' . Str::random(8)),
            
            'submitter_first_name' => $this->faker->firstName(),
            'submitter_last_name' => $this->faker->lastName(),
            'submitter_email' => $this->faker->unique()->safeEmail(),
            
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
        ];
    }
}