<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginatedIndexRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;

class ClientController extends Controller
{
    public function index(PaginatedIndexRequest $request)
    {
        return ClientResource::collection(
            Client::query()->withCount('requests')->latest('id')->paginate($request->perPage())
        )->additional(['message' => 'ok']);
    }
}
