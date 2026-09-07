<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->is_admin || $serviceRequest->client?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->is_admin || $serviceRequest->client?->user_id === $user->id;
    }

    public function delete(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->is_admin;
    }
}
