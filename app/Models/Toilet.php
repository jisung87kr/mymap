<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;

class Toilet extends Model
{
    use HasFactory;

    protected $fillable = ['주소', '도', '시', '위도', '경도'];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}