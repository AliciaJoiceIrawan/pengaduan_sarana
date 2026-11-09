<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['nama'];

    public function aspirations()
    {
        return $this->hasMany(Pengaduan::class, 'category_id');
    }
}