<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySharedSecret
{
    public function handle(Request $request, Closure $next, string $configKey): Response
    {
        $expected = (string) config($configKey);
        $provided = (string) $request->header('X-Webhook-Secret', $request->header('X-N8N-Secret', ''));

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            abort(401, 'Invalid webhook secret.');
        }

        return $next($request);
    }
}
