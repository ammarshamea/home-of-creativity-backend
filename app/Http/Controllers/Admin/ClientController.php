<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        return ClientResource::collection(
            Client::query()->withCount('requests')->latest('id')->paginate(20)
        )->additional(['message' => 'ok']);
    }
}
