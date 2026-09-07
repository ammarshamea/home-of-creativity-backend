<?php

namespace App\Http\Controllers;

use App\Actions\SubmitServiceRequest;
use App\Enums\RequestSource;
use App\Http\Requests\TelegramLinkRequest;
use App\Http\Requests\TelegramSubmitRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ServiceRequestResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

class TelegramBotController extends Controller
{
    public function link(TelegramLinkRequest $request): JsonResponse
    {
        $client = Client::query()->updateOrCreate(
            ['telegram_user_id' => $request->validated('telegram_user_id')],
            [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'locale' => $request->validated('locale') ?? 'ar',
            ],
        );

        return response()->json([
            'data' => ClientResource::make($client),
            'message' => 'Linked.',
        ]);
    }

    public function submit(TelegramSubmitRequest $request, SubmitServiceRequest $submitServiceRequest): JsonResponse
    {
        $client = Client::query()
            ->where('telegram_user_id', $request->validated('telegram_user_id'))
            ->firstOrFail();

        $serviceRequest = $submitServiceRequest->handle($client, [
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'source' => RequestSource::Telegram,
        ]);

        return response()->json([
            'data' => ServiceRequestResource::make($serviceRequest),
            'message' => 'Created.',
        ], 201);
    }
}
