<?php

namespace App\Http\Resources;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ServiceRequest */
class ServiceRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'source' => $this->source->value,
            'odoo_quotation_id' => $this->odoo_quotation_id,
            'odoo_invoice_id' => $this->odoo_invoice_id,
            'ai_analysis' => $this->ai_analysis,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'client' => ClientResource::make($this->whenLoaded('client')),
            'briefs' => $this->whenLoaded('briefs'),
            'events' => $this->whenLoaded('events'),
            'files' => $this->whenLoaded('files'),
            'revisions' => $this->whenLoaded('revisions'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
