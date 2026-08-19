<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Institution;
use App\Models\Author;
use App\Models\Document;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // ==========================================
        // 1. DUMP DATA INSTITUTIONS (KAMPUS)
        // ==========================================
        $inst1 = Institution::create([
            'name' => 'Universitas Negeri Malang',
            'country' => 'Indonesia',
            'latitude' => '-7.9628',
            'longitude' => '112.6185'
        ]);

        $inst2 = Institution::create([
            'name' => 'Universitas Brawijaya',
            'country' => 'Indonesia',
            'latitude' => '-7.9525',
            'longitude' => '112.6133'
        ]);

        $inst3 = Institution::create([
            'name' => 'Massachusetts Institute of Technology',
            'country' => 'United States',
            'latitude' => '42.3601',
            'longitude' => '-71.0942'
        ]);

        // ==========================================
        // 2. DUMP DATA AUTHORS (PENELITI)
        // ==========================================
        $auth1 = Author::create([
            'name' => 'Dwi Wulandari',
            'email' => 'dwi.wulandari@um.ac.id',
            'institution_id' => $inst1->id,
            'country' => 'Indonesia'
        ]);

        $auth2 = Author::create([
            'name' => 'Yeni Sri Wulandari',
            'email' => 'yeni.wulan@ub.ac.id',
            'institution_id' => $inst2->id,
            'country' => 'Indonesia'
        ]);

        $auth3 = Author::create([
            'name' => 'John Doe',
            'email' => 'johndoe@mit.edu',
            'institution_id' => $inst3->id,
            'country' => 'United States'
        ]);

        // ==========================================
        // 3. DUMP DATA DOCUMENTS (Lengkap dgn Publisher & SDG)
        // ==========================================
        $doc1 = Document::create([
            'document_number' => 'IDX-100001',
            'title' => 'Implementasi Green Economy pada Pendidikan Vokasi di Era Digital',
            'journal_title' => 'Journal of Sustainable Education',
            'publisher' => 'Universitas Press', // Data Publisher
            'abstract' => 'Penelitian ini membahas bagaimana integrasi kurikulum ekonomi hijau dapat meningkatkan kesadaran mahasiswa vokasi terhadap lingkungan hidup...',
            'document_type' => 'Journal Article',
            'pub_year' => 2023,
            'doi' => 'https://doi.org/10.1234/jse.v1i1.001',
            'keywords' => 'Green Economy, Vokasi, SDG 4, SDG 8, SDG 13', // Data SDG masuk di Keywords
            'pages' => 15,
            'reference_count' => 45,
            'citation_count' => 125, // Agar masuk "Most Cited"
            'views' => 450, // Agar masuk "Most Popular"
            'is_verified' => true,
            'submitter_first_name' => 'Admin',
            'submitter_last_name' => 'Sustaindex',
            'submitter_email' => 'admin@sustaindex.org',
            'verification_token' => Str::random(40),
        ]);

        $doc2 = Document::create([
            'document_number' => 'IDX-100002',
            'title' => 'Artificial Intelligence for Climate Change Mitigation in Southeast Asia',
            'journal_title' => 'International Conference on Climate Tech',
            'publisher' => 'IEEE', 
            'abstract' => 'This paper presents a novel AI model to predict extreme weather events in Southeast Asia, helping local governments prepare for disasters.',
            'document_type' => 'Conference Paper',
            'pub_year' => 2024,
            'doi' => 'https://doi.org/10.5678/icct.2024.112',
            'keywords' => 'Artificial Intelligence, Climate Change, Southeast Asia, SDG 13, SDG 11', 
            'pages' => 8,
            'reference_count' => 30,
            'citation_count' => 89,
            'views' => 320,
            'is_verified' => true,
            'submitter_first_name' => 'Admin',
            'submitter_last_name' => 'Sustaindex',
            'submitter_email' => 'admin@sustaindex.org',
            'verification_token' => Str::random(40),
        ]);

        $doc3 = Document::create([
            'document_number' => 'IDX-100003',
            'title' => 'Renewable Energy Transition Strategies in Developing Nations',
            'journal_title' => 'Energy Policy Review',
            'publisher' => 'Elsevier', 
            'abstract' => 'An analysis of policy frameworks required to accelerate the adoption of solar and wind energy in developing countries.',
            'document_type' => 'Journal Article',
            'pub_year' => 2022,
            'doi' => 'https://doi.org/10.9999/epr.2022.05.003',
            'keywords' => 'Renewable Energy, Policy, SDG 7, SDG 9', 
            'pages' => 22,
            'reference_count' => 60,
            'citation_count' => 210,
            'views' => 800,
            'is_verified' => true,
            'submitter_first_name' => 'Admin',
            'submitter_last_name' => 'Sustaindex',
            'submitter_email' => 'admin@sustaindex.org',
            'verification_token' => Str::random(40),
        ]);

        // ==========================================
        // 4. ATTACH RELASI (Penulis ke Dokumen)
        // ==========================================
        // Doc 1 ditulis oleh Dwi & Yeni
        $doc1->authors()->attach([$auth1->id, $auth2->id]);
        
        // Doc 2 ditulis oleh John Doe & Dwi
        $doc2->authors()->attach([$auth3->id, $auth1->id]);
        
        // Doc 3 ditulis oleh Yeni
        $doc3->authors()->attach([$auth2->id]);

        // ==========================================
        // 5. DUMP DATA HISTORY SITASI (Biar grafik/analytics jalan)
        // ==========================================
        $documents = [$doc1, $doc2, $doc3];
        foreach ($documents as $doc) {
            DB::table('citation_histories')->insert([
                'document_id' => $doc->id,
                'citation_count' => $doc->citation_count,
                'year' => date('Y'),
                'month' => date('m'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('Dummy data (Sustaindex) berhasil di-inject, bos! 😎🚀');
    }
}