<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function createPayment(Order $order, Payment $payment): PaymentGatewayResult;

    public function verifyWebhook(Request $request): bool;

    public function parseWebhook(Request $request): PaymentWebhookData;
}
