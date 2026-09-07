<?php

namespace App\Models;

use App\Enums\RequestSource;
use App\Enums\RequestStatus;
use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'number',
        'client_id',
        'title',
        'description',
        'status',
        'source',
        'ai_analysis',
        'odoo_quotation_id',
        'odoo_invoice_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'source' => RequestSource::class,
            'ai_analysis' => 'array',
            'paid_at' => 'datetime',
        ];
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
}
