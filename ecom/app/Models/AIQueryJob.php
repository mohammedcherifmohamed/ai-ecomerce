<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIQueryJob extends Model
{
    use HasUuids;

    protected $table = 'ai_query_jobs';

    protected $fillable = [
        'id',
        'user_id',
        'user_input',
        'status',
        'generated_sql',
        'query_result',
        'ai_response',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'query_result' => 'json',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}