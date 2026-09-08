<?php

namespace Tests\Unit;

use App\Models\ServiceRequest;
use App\Support\ResolveServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_number_strips_year_prefix(): void
    {
        $this->assertSame('7', ResolveServiceRequest::displayNumber('REQ-2026-000007'));
    }

    public function test_by_reference_accepts_full_number(): void
    {
        $request = ServiceRequest::factory()->create(['number' => 'REQ-2026-000003']);

        $resolved = app(ResolveServiceRequest::class)->byReference('REQ-2026-000003');

        $this->assertSame($request->id, $resolved->id);
    }

    public function test_by_reference_accepts_short_sequence_number(): void
    {
        $request = ServiceRequest::factory()->create(['number' => 'REQ-'.now()->year.'-000005']);

        $resolved = app(ResolveServiceRequest::class)->byReference('5');

        $this->assertSame($request->id, $resolved->id);
    }
}
