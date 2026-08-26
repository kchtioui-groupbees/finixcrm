<?php

namespace Tests\Unit;

use App\Services\RenewalService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class RenewalServiceTest extends TestCase
{
    private RenewalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RenewalService();
    }

    public function test_fifteen_days_interval(): void
    {
        $next = $this->service->calculateNextDueDate(Carbon::parse('2026-08-26'), 'day', 15);
        $this->assertSame('2026-09-10', $next->toDateString());
    }

    public function test_one_month_interval(): void
    {
        $next = $this->service->calculateNextDueDate(Carbon::parse('2026-08-26'), 'month', 1);
        $this->assertSame('2026-09-26', $next->toDateString());
    }

    public function test_two_months_interval(): void
    {
        $next = $this->service->calculateNextDueDate(Carbon::parse('2026-08-26'), 'month', 2);
        $this->assertSame('2026-10-26', $next->toDateString());
    }

    public function test_three_months_interval(): void
    {
        $next = $this->service->calculateNextDueDate(Carbon::parse('2026-08-26'), 'month', 3);
        $this->assertSame('2026-11-26', $next->toDateString());
    }

    public function test_six_months_interval(): void
    {
        $next = $this->service->calculateNextDueDate(Carbon::parse('2026-08-26'), 'month', 6);
        $this->assertSame('2027-02-26', $next->toDateString());
    }

    public function test_one_year_interval(): void
    {
        $next = $this->service->calculateNextDueDate(Carbon::parse('2026-08-26'), 'year', 1);
        $this->assertSame('2027-08-26', $next->toDateString());
    }

    public function test_january_31_plus_one_month_lands_on_last_day_of_february_non_leap_year(): void
    {
        $next = $this->service->calculateNextDueDate(Carbon::parse('2027-01-31'), 'month', 1);
        $this->assertSame('2027-02-28', $next->toDateString());
    }

    public function test_january_31_plus_one_month_lands_on_february_29_in_leap_year(): void
    {
        $next = $this->service->calculateNextDueDate(Carbon::parse('2028-01-31'), 'month', 1);
        $this->assertSame('2028-02-29', $next->toDateString());
    }

    public function test_february_29_plus_one_year_lands_on_february_28_next_year(): void
    {
        $next = $this->service->calculateNextDueDate(Carbon::parse('2028-02-29'), 'year', 1);
        $this->assertSame('2029-02-28', $next->toDateString());
    }

    public function test_unsupported_unit_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->calculateNextDueDate(Carbon::parse('2026-08-26'), 'week', 1);
    }
}
