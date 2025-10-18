<?php

namespace SagarSBhedodkar\IndexAdvisor\Models;

use Illuminate\Database\Eloquent\Model;

class IndexSuggestion extends Model
{
    protected $table = 'index_advisor_suggestions';

    protected $fillable = [
        'table',
        'columns',
        'reason',
        'examples',
        'processed_at',
    ];

    protected $casts = [
        'columns' => 'array',
        'examples' => 'array',
        'processed_at' => 'datetime',
    ];

    public $timestamps = true;
}
