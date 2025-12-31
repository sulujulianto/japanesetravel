<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Models\User;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\PaymentGatewayResult;
use App\Services\Payments\PaymentService;
use App\Services\Payments\PaymentWebhookData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_order_and_payment(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $souvenir = Souvenir::factory()->create([
            'stock' => 5,
            'price' => 100000,
        ]);

        $this->app->instance(PaymentService::class, new class extends PaymentService {
            public function driver(string $provider): PaymentGatewayInterface
            {
                return new class implements PaymentGatewayInterface {
                    public function createPayment(Order $order, Payment $payment): PaymentGatewayResult
                    {
                        return new PaymentGatewayResult(
                            providerRef: $payment->provider_ref ?? 'TEST-REF',
                            redirectUrl: 'https://pay.test/redirect',
                            token: null,
                            payload: [],
                            currency: 'IDR',
                            amount: (float) $order->total_price,
                        );
                    }

                    public function verifyWebhook(Request $request): bool
                    {
                        return true;
                    }

                    public function parseWebhook(Request $request): PaymentWebhookData
                    {
                        return new PaymentWebhookData(
                            providerRef: 'TEST-REF',
                            status: 'paid',
                            amount: 0,
                            currency: 'IDR',
                            payload: [],
                        );
                    }
                };
            }
        });

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$souvenir->id => 2]])
            ->post(route('checkout.process'), [
                'payment_provider' => 'midtrans',
            ]);

        $response->assertRedirect('https://pay.test/redirect');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('payments', [
            'provider' => 'midtrans',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'souvenir_id' => $souvenir->id,
            'quantity' => 2,
        ]);
    }
}
