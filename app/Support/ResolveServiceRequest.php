<?php

namespace App\Support;

use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResolveServiceRequest
{
    public static function displayNumber(ServiceRequest|string $request): string
    {
        $number = $request instanceof ServiceRequest ? $request->number : $request;

        if (preg_match('/REQ-\d{4}-(\d+)$/', $number, $matches) === 1) {
            return (string) (int) $matches[1];
        }

        return $number;
    }

    public function byReference(string $reference): ServiceRequest
    {
        $reference = trim($reference);
        if ($reference === '') {
            throw (new ModelNotFoundException)->setModel(ServiceRequest::class);
        }

        if (str_starts_with(strtoupper($reference), 'REQ-')) {
            return ServiceRequest::query()
                ->where('number', $reference)
                ->firstOrFail();
        }

        if (ctype_digit($reference)) {
            $numeric = (int) $reference;
            $padded = sprintf('%06d', $numeric);

            $bySequence = ServiceRequest::query()
                ->where('number', 'like', '%-'.$padded)
                ->orderByDesc('id')
                ->first();
            if ($bySequence) {
                return $bySequence;
            }

            $byId = ServiceRequest::query()->find($numeric);
            if ($byId) {
                return $byId;
            }
        }

        throw (new ModelNotFoundException)->setModel(ServiceRequest::class);
    }
}
