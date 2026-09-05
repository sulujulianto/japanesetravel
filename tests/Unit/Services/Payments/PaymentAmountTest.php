<?php

namespace Tests\Unit\Services\Payments;

use App\Services\Payments\PaymentAmount;
use PHPUnit\Framework\TestCase;

class PaymentAmountTest extends TestCase
{
    public function test_equivalent_decimal_amounts_and_currency_case_match(): void
    {
        $this->assertTrue(PaymentAmount::matches('0013.330', 'usd', '13.33', 'USD'));
        $this->assertTrue(PaymentAmount::matches('150000', 'IDR', '150000.00', 'idr'));
    }

    public function test_mismatched_or_malformed_payment_values_are_rejected(): void
    {
        $this->assertFalse(PaymentAmount::matches('13.33', 'USD', '13.34', 'USD'));
        $this->assertFalse(PaymentAmount::matches('13.33', 'USD', '13.33', 'IDR'));
        $this->assertFalse(PaymentAmount::matches('13.33', 'USD', '13.331', 'USD'));
        $this->assertFalse(PaymentAmount::matches('13.33', 'USD', '-13.33', 'USD'));
        $this->assertFalse(PaymentAmount::matches('13.33', 'USD', '1.333e1', 'USD'));
        $this->assertFalse(PaymentAmount::matches('13.33', 'US', '13.33', 'US'));
    }
}
