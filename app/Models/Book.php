<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'published_at',
    ];
    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) =>
            $attributes['image'] ? asset('storage/' . $attributes['image']) : null,
        );
    }
    protected $casts = [
        'published_at' => 'datetime',
    ];
}
