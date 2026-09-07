<?php

namespace App\Actions;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class GenerateEmployeeCode
{
    public function handle(): string
    {
        return DB::transaction(function () {
            $latest = Employee::query()
                ->where('code', 'like', 'EMP-%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('code');

            $sequence = 1;
            if (is_string($latest) && preg_match('/EMP-(\d+)$/', $latest, $matches) === 1) {
                $sequence = ((int) $matches[1]) + 1;
            }

            return sprintf('EMP-%04d', $sequence);
        });
    }
}
