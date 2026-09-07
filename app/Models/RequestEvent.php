<?php

namespace App\Models;

use App\Enums\WorkflowEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestEvent extends Model
{
    protected $fillable = [
        'request_id',
        'type',
        'payload',
        'dispatched_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => WorkflowEventType::class,
            'payload' => 'array',
            'dispatched_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }
}
