<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    protected $table = 'aspirations';

    protected $fillable = [
        'user_id',
        'category_id',
        'deskripsi',
        'status',
        'umpan_balik',
        'progres',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}