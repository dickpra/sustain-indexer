<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat Tabel Institusi (Dilengkapi Titik Koordinat Peta)
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 8)->nullable(); // Koordinat Map
            $table->decimal('longitude', 11, 8)->nullable(); // Koordinat Map
            $table->timestamps();
        });

        // 2. Buat Tabel Author (Email sebagai Unique Key)
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique(); // KTP Digital Anti-Tumpang Tindih
            $table->string('country')->nullable();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Buat Tabel Pivot (Relasi Many-to-Many antara Dokumen dan Author)
        Schema::create('author_document', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
        });

        // 4. Update Tabel Dokumen Lama
        Schema::table('documents', function (Blueprint $table) {
            $table->string('keywords')->nullable()->after('abstract'); // Tambah Keywords
            $table->dropColumn('authors'); // Hapus kolom JSON authors yang lama
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->json('authors')->nullable();
            $table->dropColumn('keywords');
        });
        Schema::dropIfExists('author_document');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('institutions');
    }
};