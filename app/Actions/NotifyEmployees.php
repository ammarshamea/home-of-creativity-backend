<?php

namespace App\Actions;

use App\Enums\EmployeeProfession;
use App\Models\Employee;
use App\Models\ServiceRequest;
use App\Services\TelegramNotifier;
use Illuminate\Support\Facades\Log;

class NotifyEmployees
{
    public function __construct(private TelegramNotifier $telegram) {}

    public function handle(ServiceRequest $request, EmployeeProfession $profession, string $text): int
    {
        $sent = 0;
        $employees = Employee::query()
            ->active()
            ->where('profession', $profession)
            ->whereNotNull('telegram_user_id')
            ->get();

        foreach ($employees as $employee) {
            if ($this->deliver((string) $employee->telegram_user_id, $text, $request->number)) {
                $sent++;
            }
        }

        if ($profession === EmployeeProfession::Sales && $sent === 0) {
            $fallback = (string) config('services.telegram.staff_chat_id');
            if ($fallback !== '' && $this->deliver($fallback, $text, $request->number)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function deliver(string $chatId, string $text, string $number): bool
    {
        $bot = $this->telegram->configured('staff') ? 'staff' : 'client';
        if (! $this->telegram->configured($bot)) {
            Log::warning('Employee Telegram notify skipped; bot is not configured.', [
                'request' => $number,
            ]);

            return false;
        }

        try {
            $this->telegram->send($chatId, $text, $bot);

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Employee Telegram notify failed.', [
                'request' => $number,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
