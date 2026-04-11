<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $guarded = [];
    // Di dalam class Institution
    protected $appends = ['lat', 'lng'];

    public function getLatAttribute() {
        return $this->attributes['latitude'];
    }

    public function getLngAttribute() {
        return $this->attributes['longitude'];
    }

    // Relasi: 1 Institusi bisa dimiliki banyak Author
    public function authors()
    {
        return $this->hasMany(Author::class);
    }
}