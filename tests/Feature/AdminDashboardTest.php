<?php

namespace Tests\Feature;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_cannot_open_admin_overview(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/overview')->assertForbidden();
    }

    public function test_admin_can_open_overview(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/overview')
            ->assertOk()
            ->assertJsonPath('data.requests', 0);
    }

    public function test_admin_payment_status_dispatches_n8n(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://n8n.test/*' => Http::response(['ok' => true], 200),
        ]);
        config(['services.n8n.webhook_url' => 'https://n8n.test/webhook/hoc-events']);

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        Sanctum::actingAs($admin);

        $serviceRequest = ServiceRequest::factory()->create([
            'status' => 'quotation_sent',
        ]);

        $this->patchJson("/api/admin/requests/{$serviceRequest->id}", [
            'status' => 'payment_confirmed',
        ])->assertOk()->assertJsonPath('data.status', 'payment_confirmed');

        Http::assertSent(fn ($request) => $request['event'] === 'PAYMENT_CONFIRMED'
            && $request['request_number'] === $serviceRequest->number);
    }

    public function test_admin_cannot_confirm_payment_before_quotation(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        Sanctum::actingAs($admin);

        $serviceRequest = ServiceRequest::factory()->create([
            'status' => 'submitted',
        ]);

        $this->patchJson("/api/admin/requests/{$serviceRequest->id}", [
            'status' => 'payment_confirmed',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.status.0', 'Payment cannot be confirmed before a quotation is sent.');

        $this->assertSame('submitted', $serviceRequest->fresh()?->status->value);
    }
}
