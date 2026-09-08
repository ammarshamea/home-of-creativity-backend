<?php

namespace Database\Seeders;

use App\Enums\EmployeeProfession;
use App\Enums\EmployeeStatus;
use App\Enums\RequestSource;
use App\Enums\RequestStatus;
use App\Models\Client;
use App\Models\Employee;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $clientUser = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Client',
                'password' => 'password',
                'phone' => '+963 968 862 822',
                'locale' => 'ar',
            ],
        );
        $clientUser->forceFill(['is_admin' => false])->save();

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'HOC Admin',
                'password' => 'password',
                'locale' => 'ar',
            ],
        );
        $admin->forceFill(['is_admin' => true])->save();

        $client = Client::query()->firstOrCreate(
            ['user_id' => $clientUser->id],
            [
                'name' => $clientUser->name,
                'email' => $clientUser->email,
                'phone' => $clientUser->phone,
                'locale' => 'ar',
            ],
        );

        if ($client->requests()->doesntExist()) {
            ServiceRequest::factory()
                ->count(6)
                ->for($client)
                ->sequence(
                    ['status' => RequestStatus::Submitted, 'source' => RequestSource::Website],
                    ['status' => RequestStatus::QuotationSent],
                    ['status' => RequestStatus::PaymentConfirmed],
                    ['status' => RequestStatus::InProgress],
                    ['status' => RequestStatus::ReadyForReview],
                    ['status' => RequestStatus::Completed, 'source' => RequestSource::Telegram],
                )
                ->create();
        }

        Employee::query()->firstOrCreate(
            ['code' => 'EMP-0001'],
            [
                'name' => 'Sales Desk',
                'phone' => '+963 000 000 000',
                'profession' => EmployeeProfession::Sales,
                'status' => EmployeeStatus::Approved,
                'notes' => 'Receives new client requests.',
                'is_active' => true,
            ],
        );
    }
}
