<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id', 'respondent_name', 'respondent_email',
        'submitted_at', 'ip_address', 'user_agent', 'is_complete'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_complete' => 'boolean',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
