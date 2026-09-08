<?php

namespace App\Models;

use App\Enums\ExecutionStatus;
use App\Enums\GeminiStatus;
use App\Enums\PaymentMethod;
use App\Enums\RequestSource;
use App\Enums\RequestStatus;
use App\Enums\WorkType;
use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'uuid',
        'number',
        'client_id',
        'title',
        'description',
        'status',
        'source',
        'work_type',
        'execution_status',
        'ai_analysis',
        'odoo_quotation_id',
        'odoo_invoice_id',
        'paid_at',
        'payment_method',
        'aggregate_version',
        'gemini_status',
        'gemini_attempts',
        'gemini_error',
        'gemini_processed_at',
        'quotation_amount',
        'quotation_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'source' => RequestSource::class,
            'work_type' => WorkType::class,
            'execution_status' => ExecutionStatus::class,
            'payment_method' => PaymentMethod::class,
            'gemini_status' => GeminiStatus::class,
            'ai_analysis' => 'array',
            'paid_at' => 'datetime',
            'gemini_processed_at' => 'datetime',
            'quotation_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $request): void {
            if (! filled($request->uuid)) {
                $request->uuid = (string) Str::uuid();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(RequestFile::class, 'request_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(RequestEvent::class, 'request_id');
    }

    public function briefs(): HasMany
    {
        return $this->hasMany(DepartmentBrief::class, 'request_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class, 'request_id');
    }

    public function clickupTasks(): HasMany
    {
        return $this->hasMany(ClickUpTask::class, 'request_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'request_id');
    }

    public function quotationDecisions(): HasMany
    {
        return $this->hasMany(QuotationDecision::class, 'request_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'request_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class, 'request_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'request_id');
    }

    public function supportMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'request_id');
    }

    public function integrationEvents(): HasMany
    {
        return $this->hasMany(IntegrationEvent::class, 'request_uuid', 'uuid');
    }
}
