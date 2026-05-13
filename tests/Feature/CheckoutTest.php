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

        $this->app->instance(PaymentService::class, new class extends PaymentService
        {
            public function driver(string $provider): PaymentGatewayInterface
            {
                return new class implements PaymentGatewayInterface
                {
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

        $this->assertSame(3, $souvenir->fresh()->stock);
    }

    public function test_checkout_restores_stock_and_cancels_order_when_gateway_creation_fails(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $souvenir = Souvenir::factory()->create([
            'stock' => 5,
            'price' => 100000,
        ]);

        $this->app->instance(PaymentService::class, new class extends PaymentService
        {
            public function driver(string $provider): PaymentGatewayInterface
            {
                return new class implements PaymentGatewayInterface
                {
                    public function createPayment(Order $order, Payment $payment): PaymentGatewayResult
                    {
                        throw new \RuntimeException('Gateway timeout');
                    }

                    public function verifyWebhook(Request $request): bool
                    {
                        return true;
                    }

                    public function parseWebhook(Request $request): PaymentWebhookData
                    {
                        return new PaymentWebhookData(
                            providerRef: 'TEST-REF',
                            status: 'pending',
                            amount: 0,
                            currency: 'IDR',
                            payload: [],
                        );
                    }
                };
            }
        });

        $response = $this->actingAs($user)
            ->from(route('cart.index'))
            ->withSession(['cart' => [$souvenir->id => 2]])
            ->post(route('checkout.process'), [
                'payment_provider' => 'midtrans',
            ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
        $response->assertSessionHas('cart.'.$souvenir->id, 2);

        $order = Order::first();
        $payment = Payment::first();
        $this->assertNotNull($order);
        $this->assertNotNull($payment);

        $this->assertSame('cancelled', $order->status);
        $this->assertSame('failed', $payment->status);
        $this->assertStringContainsString('Gateway timeout', (string) ($payment->payload_json['error'] ?? ''));
        $this->assertSame(5, $souvenir->fresh()->stock);
    }

    public function test_checkout_failure_restores_stock_for_all_items(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $souvenirA = Souvenir::factory()->create([
            'stock' => 10,
            'price' => 50000,
        ]);
        $souvenirB = Souvenir::factory()->create([
            'stock' => 8,
            'price' => 70000,
        ]);

        $this->app->instance(PaymentService::class, new class extends PaymentService
        {
            public function driver(string $provider): PaymentGatewayInterface
            {
                return new class implements PaymentGatewayInterface
                {
                    public function createPayment(Order $order, Payment $payment): PaymentGatewayResult
                    {
                        throw new \RuntimeException('Gateway unavailable');
                    }

                    public function verifyWebhook(Request $request): bool
                    {
                        return true;
                    }

                    public function parseWebhook(Request $request): PaymentWebhookData
                    {
                        return new PaymentWebhookData(
                            providerRef: 'TEST-REF',
                            status: 'pending',
                            amount: 0,
                            currency: 'IDR',
                            payload: [],
                        );
                    }
                };
            }
        });

        $response = $this->actingAs($user)
            ->from(route('cart.index'))
            ->withSession([
                'cart' => [
                    $souvenirA->id => 3,
                    $souvenirB->id => 2,
                ],
            ])
            ->post(route('checkout.process'), [
                'payment_provider' => 'midtrans',
            ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('cart.'.$souvenirA->id, 3);
        $response->assertSessionHas('cart.'.$souvenirB->id, 2);

        $this->assertSame(10, $souvenirA->fresh()->stock);
        $this->assertSame(8, $souvenirB->fresh()->stock);
        $this->assertLessThanOrEqual(10, $souvenirA->fresh()->stock);
        $this->assertLessThanOrEqual(8, $souvenirB->fresh()->stock);
    }
}
