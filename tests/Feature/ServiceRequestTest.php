<?php

namespace Tests\Feature;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_request(): void
    {
        $this->postJson('/api/requests', [
            'title' => 'Booth',
            'description' => 'Need an exhibition booth.',
        ])->assertUnauthorized();
    }

    public function test_owner_can_create_and_list_own_request(): void
    {
        Http::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/requests', [
            'title' => 'Brand system',
            'description' => 'Full identity for a new hall.',
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Brand system')
            ->assertJsonPath('data.status', 'submitted');

        $this->getJson('/api/requests')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_other_user_cannot_view_request(): void
    {
        $owner = User::factory()->create();
        Sanctum::actingAs($owner);
        Http::fake();

        $id = $this->postJson('/api/requests', [
            'title' => 'Private brief',
            'description' => 'Confidential work.',
        ])->json('data.id');

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/requests/{$id}")->assertForbidden();
    }

    public function test_validation_requires_title(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/requests', [
            'description' => 'Missing title.',
        ])->assertUnprocessable();
    }

    public function test_n8n_callback_updates_status(): void
    {
        Http::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $number = $this->postJson('/api/requests', [
            'title' => 'Paid ads',
            'description' => 'Launch campaign.',
        ])->json('data.number');

        $this->withHeaders(['X-N8N-Secret' => 'change-me'])
            ->postJson('/api/webhooks/n8n', [
                'event' => 'QUOTATION_READY',
                'request_number' => $number,
                'payload' => ['odoo_quotation_id' => 'Q-100'],
            ])->assertOk()
            ->assertJsonPath('data.status', 'quotation_sent');
    }

    public function test_creating_request_dispatches_n8n_event(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://n8n.test/*' => Http::response(['ok' => true], 200),
        ]);

        config(['services.n8n.webhook_url' => 'https://n8n.test/webhook/hoc-events']);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/requests', [
            'title' => 'Exhibition booth',
            'description' => 'Need a 3D booth for the next fair.',
        ])->assertCreated();

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://n8n.test/webhook/hoc-events'
                && $request->hasHeader('X-N8N-Secret', 'change-me')
                && $request['event'] === 'REQUEST_SUBMITTED'
                && $request['payload']['title'] === 'Exhibition booth';
        });

        $this->assertNotNull(ServiceRequest::query()->first()?->events()->first()?->dispatched_at);
    }

    public function test_n8n_callback_rejects_bad_secret(): void
    {
        Http::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $number = $this->postJson('/api/requests', [
            'title' => 'Secret check',
            'description' => 'Webhook auth.',
        ])->json('data.number');

        $this->withHeaders(['X-N8N-Secret' => 'wrong-secret'])
            ->postJson('/api/webhooks/n8n', [
                'event' => 'QUOTATION_READY',
                'request_number' => $number,
            ])->assertUnauthorized();
    }

    public function test_n8n_request_submitted_sets_analyzing(): void
    {
        Http::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $number = $this->postJson('/api/requests', [
            'title' => 'Visual identity',
            'description' => 'Logo and brand system.',
        ])->json('data.number');

        $this->withHeaders(['X-N8N-Secret' => 'change-me'])
            ->postJson('/api/webhooks/n8n', [
                'event' => 'REQUEST_SUBMITTED',
                'request_number' => $number,
                'payload' => [
                    'ai_analysis' => [
                        'summary' => 'Brand intake',
                        'departments' => ['branding'],
                    ],
                ],
            ])->assertOk()
            ->assertJsonPath('data.status', 'ai_analyzing')
            ->assertJsonPath('data.ai_analysis.summary', 'Brand intake');
    }

    public function test_n8n_tasks_ready_creates_briefs(): void
    {
        Http::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $number = $this->postJson('/api/requests', [
            'title' => 'Website',
            'description' => 'Marketing site and dashboard.',
        ])->json('data.number');

        $this->withHeaders(['X-N8N-Secret' => 'change-me'])
            ->postJson('/api/webhooks/n8n', [
                'event' => 'TASKS_READY',
                'request_number' => $number,
                'payload' => [
                    'briefs' => [
                        [
                            'department' => 'web',
                            'brief' => 'Build the marketing site.',
                            'clickup_task_id' => 'CU-100',
                        ],
                    ],
                ],
            ])->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertSame('web', ServiceRequest::query()->first()?->briefs()->first()?->department);
    }
}
