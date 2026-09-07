<?php

namespace Tests\Feature;

use App\Enums\EmployeeProfession;
use App\Models\Employee;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_employees(): void
    {
        $this->getJson('/api/admin/employees')->assertUnauthorized();
    }

    public function test_client_cannot_create_employee(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/admin/employees', [
            'name' => 'Sara',
            'profession' => 'sales',
        ])->assertForbidden();
    }

    public function test_admin_can_create_and_list_employees(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/admin/employees', [
            'name' => 'Sara Saleh',
            'phone' => '+963 999 000 111',
            'telegram_user_id' => '555001',
            'clickup_user_id' => '88821',
            'profession' => 'sales',
            'notes' => 'Morning shift',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Sara Saleh')
            ->assertJsonPath('data.profession', 'sales')
            ->assertJsonPath('data.code', 'EMP-0001');

        $this->getJson('/api/admin/employees')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_employee_name_is_required(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/admin/employees', [
            'profession' => 'sales',
        ])->assertUnprocessable();
    }

    public function test_new_request_notifies_sales_employees(): void
    {
        Http::preventStrayRequests();
        config([
            'services.telegram.staff_bot_token' => 'staff-token',
            'services.telegram.bot_token' => 'client-token',
        ]);
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
            'https://n8n.test/*' => Http::response(['ok' => true], 200),
            '*' => Http::response(['ok' => true], 200),
        ]);

        Employee::factory()->sales()->create([
            'telegram_user_id' => '6350001',
        ]);

        $this->withHeaders(['X-Webhook-Secret' => 'change-me-bot'])
            ->postJson('/api/bot/telegram/link', [
                'telegram_user_id' => 'tg-client-1',
                'name' => 'Client One',
                'locale' => 'ar',
            ])->assertOk();

        $this->withHeaders(['X-Webhook-Secret' => 'change-me-bot'])
            ->postJson('/api/bot/telegram/requests', [
                'telegram_user_id' => 'tg-client-1',
                'title' => 'Booth for sales',
                'description' => 'Need a quotation.',
            ])->assertCreated();

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'botstaff-token/sendMessage')
            && $request['chat_id'] === '6350001'
            && str_contains((string) $request['text'], 'طلب جديد لقسم المبيعات'));
    }

    public function test_staff_bot_forwards_a_reply_to_the_client_bot(): void
    {
        Http::preventStrayRequests();
        config(['services.telegram.bot_token' => 'client-token']);
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        Employee::factory()->sales()->create([
            'telegram_user_id' => '6350001',
        ]);
        $serviceRequest = ServiceRequest::factory()->create();
        $serviceRequest->client?->forceFill(['telegram_user_id' => 'tg-client-9'])->save();

        $this->withHeaders(['X-Webhook-Secret' => 'change-me-staff'])
            ->postJson('/api/bot/staff/reply', [
                'telegram_user_id' => '6350001',
                'request_number' => $serviceRequest->number,
                'text' => 'سنرسل العرض غداً',
            ])->assertOk()
            ->assertJsonPath('data.sent', true);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'botclient-token/sendMessage')
            && $request['chat_id'] === 'tg-client-9'
            && str_contains((string) $request['text'], 'سنرسل العرض غداً'));
    }

    public function test_unknown_staff_cannot_reply(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();

        $this->withHeaders(['X-Webhook-Secret' => 'change-me-staff'])
            ->postJson('/api/bot/staff/reply', [
                'telegram_user_id' => 'not-on-file',
                'request_number' => $serviceRequest->number,
                'text' => 'Hello',
            ])->assertNotFound();
    }

    public function test_admin_can_update_and_delete_an_employee(): void
    {
        Sanctum::actingAs($this->admin());
        $employee = Employee::factory()->create(['profession' => EmployeeProfession::Media]);

        $this->putJson("/api/admin/employees/{$employee->id}", [
            'name' => 'Updated Name',
            'profession' => 'web',
        ])->assertOk()->assertJsonPath('data.profession', 'web');

        $this->deleteJson("/api/admin/employees/{$employee->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Deleted.');
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
