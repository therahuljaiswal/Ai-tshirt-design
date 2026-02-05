<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prompt',
        'image_path',
        'is_watermarked',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
