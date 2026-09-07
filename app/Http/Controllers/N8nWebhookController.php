<?php

namespace App\Http\Controllers;

use App\Actions\ApplyN8nCallback;
use App\Http\Requests\N8nCallbackRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;

class N8nWebhookController extends Controller
{
    public function __invoke(N8nCallbackRequest $request, ApplyN8nCallback $applyN8nCallback): ServiceRequestResource
    {
        $serviceRequest = ServiceRequest::query()
            ->where('number', $request->validated('request_number'))
            ->firstOrFail();

        $updated = $applyN8nCallback->handle(
            $serviceRequest,
            $request->validated('event'),
            $request->validated('payload') ?? [],
        );

        return ServiceRequestResource::make($updated)
            ->additional(['message' => 'Workflow updated.']);
    }
}
