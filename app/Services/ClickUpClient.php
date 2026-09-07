<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClickUpClient
{
    public function configured(): bool
    {
        return filled(config('services.clickup.token'))
            && filled(config('services.clickup.list_id'));
    }

    /**
     * @param  array<int, array{department: string, brief: string}>  $briefs
     * @return array<int, array{department: string, brief: string, clickup_task_id: string}>
     */
    public function createTasks(string $requestNumber, array $briefs): array
    {
        $listId = (string) config('services.clickup.list_id');
        $created = [];

        foreach ($briefs as $brief) {
            $response = Http::timeout((int) config('services.clickup.timeout', 12))
                ->connectTimeout(3)
                ->retry([200, 500])
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => (string) config('services.clickup.token'),
                ])
                ->post('https://api.clickup.com/api/v2/list/'.$listId.'/task', [
                    'name' => $requestNumber.' · '.$brief['department'],
                    'description' => $brief['brief'],
                    'tags' => ['hoc', $brief['department']],
                ])
                ->throw();

            $taskId = $response->json('id');
            if (! is_string($taskId) || $taskId === '') {
                throw new RuntimeException('ClickUp did not return a task id.');
            }

            $created[] = [
                'department' => $brief['department'],
                'brief' => $brief['brief'],
                'clickup_task_id' => $taskId,
            ];
        }

        return $created;
    }
}
