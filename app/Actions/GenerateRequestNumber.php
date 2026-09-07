<?php

namespace App\Actions;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;

class GenerateRequestNumber
{
    public function handle(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $latest = ServiceRequest::query()
                ->where('number', 'like', "REQ-{$year}-%")
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('number');

            $sequence = 1;
            if (is_string($latest) && preg_match('/REQ-\d{4}-(\d+)$/', $latest, $matches) === 1) {
                $sequence = ((int) $matches[1]) + 1;
            }

            return sprintf('REQ-%d-%06d', $year, $sequence);
        });
    }
}
