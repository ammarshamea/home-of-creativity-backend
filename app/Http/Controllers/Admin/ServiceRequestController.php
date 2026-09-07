<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DispatchStatusWorkflow;
use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateServiceRequestStatusRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceRequest::query()->with('client')->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return ServiceRequestResource::collection($query->paginate(20))
            ->additional(['message' => 'ok']);
    }

    public function show(ServiceRequest $serviceRequest): ServiceRequestResource
    {
        $serviceRequest->load(['client', 'briefs', 'events', 'files', 'revisions']);

        return ServiceRequestResource::make($serviceRequest)
            ->additional(['message' => 'ok']);
    }

    public function update(
        UpdateServiceRequestStatusRequest $request,
        ServiceRequest $serviceRequest,
        DispatchStatusWorkflow $dispatchStatusWorkflow,
    ): ServiceRequestResource {
        $status = RequestStatus::from($request->validated('status'));
        $serviceRequest->forceFill(['status' => $status])->save();
        $dispatchStatusWorkflow->handle($serviceRequest, $status);
        $serviceRequest->load('client');

        return ServiceRequestResource::make($serviceRequest)
            ->additional(['message' => 'Updated.']);
    }
}
