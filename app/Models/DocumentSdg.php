<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSdg extends Model
{
    protected $fillable = ['document_id', 'sdg_code', 'sdg_name'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}