<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'surname',
        'name',
        'patronymic',
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }


    public function getFullNameAttribute()
    {
        return trim("{$this->surname} {$this->name} {$this->patronymic}");
    }
}
