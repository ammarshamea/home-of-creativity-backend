<?php

namespace App\Http\Controllers;

use App\Actions\EnsureClientForUser;
use App\Actions\SubmitServiceRequest;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $user = $request->user();
        $query = ServiceRequest::query()->with('client')->latest('id');

        if (! $user?->is_admin) {
            $query->whereHas('client', fn ($builder) => $builder->where('user_id', $user?->id));
        }

        return ServiceRequestResource::collection($query->paginate(15))
            ->additional(['message' => 'ok']);
    }

    public function store(
        StoreServiceRequestRequest $request,
        EnsureClientForUser $ensureClient,
        SubmitServiceRequest $submitServiceRequest,
    ): JsonResponse {
        $this->authorize('create', ServiceRequest::class);

        $client = $ensureClient->handle($request->user());
        $serviceRequest = $submitServiceRequest->handle($client, $request->validated());

        return ServiceRequestResource::make($serviceRequest)
            ->additional(['message' => 'Created.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(ServiceRequest $serviceRequest): ServiceRequestResource
    {
        $this->authorize('view', $serviceRequest);

        $serviceRequest->load('client');

        return ServiceRequestResource::make($serviceRequest)
            ->additional(['message' => 'ok']);
    }
}
