<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffReplyRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\ServiceRequest;
use App\Services\TelegramNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StaffBotController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $telegramId = (string) $request->query('telegram_user_id', '');
        $employee = Employee::query()
            ->active()
            ->where('telegram_user_id', $telegramId)
            ->first();

        if (! $employee) {
            return response()->json([
                'data' => null,
                'message' => 'Employee is not registered.',
            ], 404);
        }

        return response()->json([
            'data' => EmployeeResource::make($employee),
            'message' => 'ok',
        ]);
    }

    public function reply(StaffReplyRequest $request, TelegramNotifier $telegram): JsonResponse
    {
        $employee = Employee::query()
            ->active()
            ->where('telegram_user_id', $request->validated('telegram_user_id'))
            ->firstOrFail();

        $serviceRequest = ServiceRequest::query()
            ->with('client')
            ->where('number', $request->validated('request_number'))
            ->firstOrFail();

        $chatId = $serviceRequest->client?->telegram_user_id;
        if (! filled($chatId)) {
            throw ValidationException::withMessages([
                'request_number' => 'This client has no Telegram conversation.',
            ]);
        }

        if (! $telegram->configured('client')) {
            throw ValidationException::withMessages([
                'text' => 'The client Telegram bot is not configured.',
            ]);
        }

        $telegram->send(
            (string) $chatId,
            "رسالة من {$employee->name} بخصوص {$serviceRequest->number}:\n\n".$request->validated('text'),
            'client',
        );

        return response()->json([
            'data' => [
                'sent' => true,
                'request_number' => $serviceRequest->number,
            ],
            'message' => 'Forwarded.',
        ]);
    }
}
