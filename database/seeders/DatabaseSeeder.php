<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\Author;
use App\Models\Institution;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat 20 Kampus/Institusi Palsu
        for ($i = 0; $i < 20; $i++) {
            Institution::create([
                'name' => fake()->company() . ' University',
                'country' => fake()->country(),
            ]);
        }

        // 2. Buat 100 Author Palsu & masukkan ke kampus secara acak
        $institutions = Institution::pluck('id')->toArray();
        for ($i = 0; $i < 100; $i++) {
            Author::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'country' => fake()->country(),
                'institution_id' => fake()->randomElement($institutions),
            ]);
        }

        // 3. Buat 5.000 Dokumen Palsu & Tautkan (Relasikan) ke Author!
        $authors = Author::pluck('id')->toArray();

        Document::factory(5000)->create()->each(function ($document) use ($authors) {
            // Ambil 1 sampai 3 Author ID secara acak
            $randomAuthorIds = fake()->randomElements($authors, rand(1, 3));
            // Sambungkan jurnal dengan author tersebut ke tabel pivot
            $document->authors()->attach($randomAuthorIds);
        });
    }
}