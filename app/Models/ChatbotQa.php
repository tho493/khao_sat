<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotQa extends Model
{
    use HasFactory;

    protected $table = 'chatbot_qa';

    protected $fillable = [
        'keywords',
        'question',
        'answer',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];
}