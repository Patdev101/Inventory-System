<?php

namespace Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class FormattingHelpersTest extends TestCase
{
    public function test_format_qty_trims_trailing_zeros(): void
    {
        $this->assertSame('12', format_qty(12.0000));
        $this->assertSame('5.5', format_qty(5.5000));
        $this->assertSame('2.1', format_qty(2.1000));
        $this->assertSame('0', format_qty(0));
    }

    public function test_format_qty_handles_negative_values(): void
    {
        $this->assertSame('-5', format_qty(-5.0000));
        $this->assertSame('-2.5', format_qty(-2.5000));
    }

    public function test_format_qty_rounds_to_two_decimals(): void
    {
        $this->assertSame('1.23', format_qty(1.2345));
        $this->assertSame('1.24', format_qty(1.2367));
    }

    public function test_format_qty_handles_large_values(): void
    {
        $this->assertSame('1000000', format_qty(1000000.0000));
        $this->assertSame('999999.99', format_qty(999999.99));
    }

    public function test_format_qty_handles_string_input(): void
    {
        $this->assertSame('12', format_qty('12.0000'));
        $this->assertSame('5.5', format_qty('5.5'));
    }

    public function test_format_date_returns_month_day_year(): void
    {
        $date = Carbon::create(2026, 9, 8, 14, 5);

        $this->assertSame('09/08/2026', format_date($date));
    }

    public function test_format_date_returns_null_for_falsy_input(): void
    {
        $this->assertNull(format_date(null));
        $this->assertNull(format_date(''));
    }

    public function test_format_date_accepts_a_date_string(): void
    {
        $this->assertSame('01/15/2026', format_date('2026-01-15'));
    }

    public function test_format_datetime_includes_12_hour_time_with_am_pm(): void
    {
        $morning = Carbon::create(2026, 9, 8, 9, 5);
        $afternoon = Carbon::create(2026, 9, 8, 14, 5);

        $this->assertSame('09/08/2026 09:05 AM', format_datetime($morning));
        $this->assertSame('09/08/2026 02:05 PM', format_datetime($afternoon));
    }

    public function test_format_datetime_returns_null_for_falsy_input(): void
    {
        $this->assertNull(format_datetime(null));
    }
}
