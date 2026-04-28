<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->string('title');
            $table->text('authors'); // Akan menyimpan data format JSON Array
            $table->text('abstract');
            
            // Info Dokumen
            $table->string('document_type')->nullable();
            $table->string('pub_year')->nullable();
            $table->integer('pages')->nullable();
            $table->integer('reference_count')->nullable();
            $table->boolean('is_peer_reviewed')->default(false);
            
            // Data Submitter
            $table->string('submitter_first_name');
            $table->string('submitter_last_name');
            $table->string('submitter_email');
            
            // Ekstraksi Sistem
            $table->string('doi')->nullable();
            $table->integer('citation_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->string('verification_token')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};