<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OdooClient
{
    public function configured(): bool
    {
        return (bool) config('services.odoo.enabled')
            && filled(config('services.odoo.url'))
            && filled(config('services.odoo.db'))
            && filled(config('services.odoo.username'))
            && filled(config('services.odoo.api_key'));
    }

    /**
     * @return array{odoo_partner_id: string, odoo_quotation_id: string}
     */
    public function createQuotation(string $partnerName, ?string $email, ?string $phone, string $requestNumber, string $title): array
    {
        $partnerId = $this->createOrReusePartner($partnerName, $email, $phone, $requestNumber);
        $uid = $this->authenticate();
        $orderId = $this->execute($uid, 'sale.order', 'create', [[
            'partner_id' => (int) $partnerId,
            'client_order_ref' => $requestNumber,
            'origin' => $requestNumber,
            'note' => $title,
        ]]);

        return [
            'odoo_partner_id' => (string) $partnerId,
            'odoo_quotation_id' => (string) $orderId,
        ];
    }

    public function createOrReusePartner(string $partnerName, ?string $email, ?string $phone, string $requestNumber): string
    {
        $uid = $this->authenticate();

        if (filled($email)) {
            $existing = $this->execute($uid, 'res.partner', 'search', [[['email', '=', $email]], 0, 1]);
            if (is_array($existing) && isset($existing[0])) {
                return (string) $existing[0];
            }
        }

        $partnerId = $this->execute($uid, 'res.partner', 'create', [[
            'name' => $partnerName,
            'email' => $email,
            'phone' => $phone,
            'comment' => 'HOC '.$requestNumber,
        ]]);

        return (string) $partnerId;
    }

    public function createInvoice(string $partnerId, string $requestNumber, ?string $quotationId = null): string
    {
        $uid = $this->authenticate();

        return (string) $this->execute($uid, 'account.move', 'create', [[
            'move_type' => 'out_invoice',
            'partner_id' => (int) $partnerId,
            'invoice_origin' => $quotationId ?: $requestNumber,
            'ref' => $requestNumber,
        ]]);
    }

    private function authenticate(): int
    {
        $uid = $this->jsonrpc('common', 'authenticate', [
            config('services.odoo.db'),
            config('services.odoo.username'),
            config('services.odoo.api_key'),
            [],
        ]);

        if (! is_int($uid) && ! is_numeric($uid)) {
            throw new RuntimeException('Odoo authentication failed.');
        }

        return (int) $uid;
    }

    private function execute(int $uid, string $model, string $method, array $args): mixed
    {
        return $this->jsonrpc('object', 'execute_kw', [
            config('services.odoo.db'),
            $uid,
            config('services.odoo.api_key'),
            $model,
            $method,
            $args,
        ]);
    }

    private function jsonrpc(string $service, string $method, array $args): mixed
    {
        $url = rtrim((string) config('services.odoo.url'), '/').'/jsonrpc';
        $response = Http::timeout((int) config('services.odoo.timeout', 12))
            ->connectTimeout(3)
            ->retry([200, 500])
            ->acceptJson()
            ->post($url, [
                'jsonrpc' => '2.0',
                'method' => 'call',
                'params' => [
                    'service' => $service,
                    'method' => $method,
                    'args' => $args,
                ],
                'id' => random_int(1, 9999),
            ])
            ->throw();

        $payload = $response->json();
        if (isset($payload['error'])) {
            $message = data_get($payload, 'error.data.message')
                ?? data_get($payload, 'error.message')
                ?? 'Odoo request failed.';
            throw new RuntimeException((string) $message);
        }

        return $payload['result'] ?? null;
    }
}
