<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $guarded = [];

    // Relasi: 1 Author bisa punya banyak Dokumen Jurnal
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'author_document');
    }

    // Relasi: 1 Author berada di 1 Institusi
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}