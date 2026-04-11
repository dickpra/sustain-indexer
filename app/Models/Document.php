<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number', 'title', 'authors', 'abstract',
        'document_type', 'pub_year', 'pages', 'reference_count',
        'is_peer_reviewed', 'submitter_first_name', 'submitter_last_name', 
        'submitter_email', 'doi', 'is_verified', 'verification_token'
    ];

    // Beritahu Laravel kalau kolom authors itu isinya Array/JSON
    protected $casts = [
        'authors' => 'array',
    ];

    // Relasi: 1 Dokumen Jurnal ditulis oleh banyak Author
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_document');
    }
    
}
