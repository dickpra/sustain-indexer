<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('document_sdgs', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel documents (otomatis terhapus kalau dokumen dihapus)
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            
            $table->string('sdg_code'); // Contoh: "SDG 1"
            $table->string('sdg_name'); // Contoh: "SDG 1: No Poverty"
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_sdgs');
    }
};