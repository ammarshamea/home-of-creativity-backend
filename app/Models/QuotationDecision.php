<?php

namespace App\Models;

use App\Enums\QuotationDecisionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationDecision extends Model
{
    protected $fillable = [
        'request_id',
        'quotation_id',
        'decision',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'decision' => QuotationDecisionType::class,
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
