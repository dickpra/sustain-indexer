<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        // Membuat judul jurnal yang terdengar ilmiah (sekitar 5-10 kata)
        $title = Str::title($this->faker->words(rand(5, 10), true));

        // Membuat 1 sampai 3 nama author palsu
        $authors = [];
        for ($i = 0; $i < rand(1, 3); $i++) {
            $authors[] = $this->faker->name();
        }

        return [
            'document_number' => 'IDX-' . $this->faker->unique()->randomNumber(6, true),
            'title' => $title,
            // Simpan sebagai JSON string karena format di DB kita kemarin array/json
            'authors' => json_encode($authors), 
            'abstract' => $this->faker->paragraphs(rand(2, 4), true),
            'document_type' => $this->faker->randomElement(['Book', 'Journal Article', 'Conference Paper', 'Report']),
            'pub_year' => $this->faker->numberBetween(2010, 2026),
            'pages' => $this->faker->numberBetween(5, 50),
            'reference_count' => $this->faker->numberBetween(10, 100),
            
            // Anggap semua data palsu ini sudah diverifikasi agar muncul di pencarian
            'is_verified' => true, 
            'verification_token' => Str::random(40),
            
            // 70% jurnal punya DOI palsu, sisanya null
            'doi' => $this->faker->optional(0.7)->passthrough('https://doi.org/10.' . $this->faker->numberBetween(1000, 9999) . '/' . Str::random(8)),
            
            // Data Submitter
            'submitter_first_name' => $this->faker->firstName(),
            'submitter_last_name' => $this->faker->lastName(),
            'submitter_email' => $this->faker->unique()->safeEmail(),
            
            // Tanggal dibuat diacak dari 2 tahun lalu sampai hari ini
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
        ];
    }
}
