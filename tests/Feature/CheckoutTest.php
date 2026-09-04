<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\PaymentGatewayResult;
use App\Services\Payments\PaymentService;
use App\Services\Payments\PaymentWebhookData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $address = $this->createAddress($user);

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
                'shipping_address_id' => $address->id,
            ]);

        $response->assertRedirect('https://pay.test/redirect');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
            'status' => 'pending',
        ]);

        $order = Order::firstOrFail();
        $this->assertSame('Edo Wardana', $order->shipping_address_snapshot['recipient_name']);
        $this->assertSame('Jalan Sakura Nomor 10', $order->shipping_address_snapshot['address_line_1']);

        $rawSnapshot = (string) DB::table('orders')->where('id', $order->id)->value('shipping_address_snapshot');
        $this->assertStringNotContainsString('Edo Wardana', $rawSnapshot);
        $this->assertStringNotContainsString('Jalan Sakura Nomor 10', $rawSnapshot);

        $address->update(['recipient_name' => 'Nama Baru']);
        $address->delete();
        $order->refresh();

        $this->assertNull($order->shipping_address_id);
        $this->assertSame('Edo Wardana', $order->shipping_address_snapshot['recipient_name']);

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Edo Wardana')
            ->assertSee('Jalan Sakura Nomor 10');

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
        $address = $this->createAddress($user);

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
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->from(route('cart.index'))
            ->withSession(['cart' => [$souvenir->id => 2]])
            ->post(route('checkout.process'), [
                'payment_provider' => 'midtrans',
                'shipping_address_id' => $address->id,
            ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error', 'Failed to create payment. Please try again.');
        $response->assertSessionHas('cart.'.$souvenir->id, 2);

        $order = Order::first();
        $payment = Payment::first();
        $this->assertNotNull($order);
        $this->assertNotNull($payment);

        $this->assertSame('cancelled', $order->status);
        $this->assertNotNull($order->stock_restored_at);
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
        $address = $this->createAddress($user);

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
                'shipping_address_id' => $address->id,
            ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('cart.'.$souvenirA->id, 3);
        $response->assertSessionHas('cart.'.$souvenirB->id, 2);

        $this->assertSame(10, $souvenirA->fresh()->stock);
        $this->assertSame(8, $souvenirB->fresh()->stock);
        $this->assertLessThanOrEqual(10, $souvenirA->fresh()->stock);
        $this->assertLessThanOrEqual(8, $souvenirB->fresh()->stock);
    }

    public function test_checkout_with_stale_cart_item_is_rejected_and_cart_is_sanitized(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $validSouvenir = Souvenir::factory()->create([
            'stock' => 5,
            'price' => 50000,
        ]);

        $staleId = 999999;

        $response = $this->actingAs($user)
            ->from(route('cart.index'))
            ->withSession([
                'cart' => [
                    $validSouvenir->id => 2,
                    $staleId => 1,
                ],
            ])
            ->post(route('checkout.process'), [
                'payment_provider' => 'midtrans',
            ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
        $response->assertSessionHas('cart.'.$validSouvenir->id, 2);
        $response->assertSessionMissing('cart.'.$staleId);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(5, $validSouvenir->fresh()->stock);
    }

    public function test_cart_displays_the_authenticated_users_saved_shipping_addresses(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $souvenir = Souvenir::factory()->create(['stock' => 5]);
        $address = $this->createAddress($user);
        $this->createAddress($otherUser, [
            'recipient_name' => 'Pengguna Lain',
            'address_line_1' => 'Jalan Milik Pengguna Lain',
        ]);

        $this->actingAs($user)
            ->withSession(['cart' => [$souvenir->id => 1]])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('name="shipping_address_id"', false)
            ->assertSee('value="'.$address->id.'"', false)
            ->assertSee('Edo Wardana')
            ->assertSee('Jalan Sakura Nomor 10')
            ->assertDontSee('Pengguna Lain')
            ->assertDontSee('Jalan Milik Pengguna Lain');
    }

    public function test_checkout_requires_a_saved_shipping_address(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $souvenir = Souvenir::factory()->create([
            'stock' => 5,
            'price' => 100000,
        ]);

        $this->actingAs($user)
            ->from(route('cart.index'))
            ->withSession(['cart' => [$souvenir->id => 1]])
            ->post(route('checkout.process'), [
                'payment_provider' => 'midtrans',
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('shipping_address_id');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $souvenir->fresh()->stock);
    }

    public function test_checkout_rejects_another_users_shipping_address(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $souvenir = Souvenir::factory()->create([
            'stock' => 5,
            'price' => 100000,
        ]);
        $otherAddress = $this->createAddress($otherUser);

        $this->actingAs($user)
            ->from(route('cart.index'))
            ->withSession(['cart' => [$souvenir->id => 1]])
            ->post(route('checkout.process'), [
                'payment_provider' => 'midtrans',
                'shipping_address_id' => $otherAddress->id,
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('shipping_address_id');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $souvenir->fresh()->stock);
    }

    public function test_retry_payment_reuses_existing_pending_payment_redirect_url_without_creating_new_payment(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 200000,
            'status' => 'pending',
            'note' => 'Retry test order',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-RETRY-PENDING-001',
            'status' => 'pending',
            'amount' => 200000,
            'currency' => 'IDR',
            'payload_json' => [
                'gateway' => [
                    'redirect_url' => 'https://pay.test/existing-midtrans',
                ],
            ],
        ]);

        $response = $this->actingAs($user)
            ->post(route('orders.pay', $order), [
                'payment_provider' => 'paypal',
            ]);

        $response->assertRedirect('https://pay.test/existing-midtrans');

        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
        $this->assertTrue($payment->fresh()->is($payment));
        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_retry_payment_with_pending_payment_without_redirect_url_does_not_create_duplicate_payment(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 120000,
            'status' => 'pending',
            'note' => 'Retry pending without URL',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-RETRY-NO-URL-001',
            'status' => 'pending',
            'amount' => 120000,
            'currency' => 'IDR',
            'payload_json' => [
                'gateway' => [],
            ],
        ]);

        $response = $this->actingAs($user)
            ->post(route('orders.pay', $order), [
                'payment_provider' => 'midtrans',
            ]);

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('error');
        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
    }

    public function test_retry_payment_creates_new_payment_after_failed_payment(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 190000,
            'status' => 'pending',
            'note' => 'Retry after failed',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'provider_ref' => 'ORD-RETRY-FAILED-001',
            'status' => 'failed',
            'amount' => 190000,
            'currency' => 'IDR',
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
                            providerRef: $payment->provider_ref ?? 'RETRY-REF',
                            redirectUrl: 'https://pay.test/retry-success',
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
                            providerRef: 'RETRY-REF',
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
            ->post(route('orders.pay', $order), [
                'payment_provider' => 'midtrans',
            ]);

        $response->assertRedirect('https://pay.test/retry-success');
        $this->assertSame(2, Payment::where('order_id', $order->id)->count());
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_retry_payment_for_completed_order_is_rejected_without_creating_payment(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 220000,
            'status' => 'completed',
            'note' => 'Completed order',
        ]);

        $response = $this->actingAs($user)
            ->post(route('orders.pay', $order), [
                'payment_provider' => 'midtrans',
            ]);

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('error');
        $this->assertSame(0, Payment::where('order_id', $order->id)->count());
    }

    public function test_retry_payment_for_cancelled_order_is_rejected_without_creating_payment(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 230000,
            'status' => 'cancelled',
            'note' => 'Cancelled order',
        ]);

        $response = $this->actingAs($user)
            ->post(route('orders.pay', $order), [
                'payment_provider' => 'paypal',
            ]);

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('error');
        $this->assertSame(0, Payment::where('order_id', $order->id)->count());
    }

    public function test_retry_payment_for_other_user_order_is_forbidden(): void
    {
        $owner = User::factory()->create([
            'role' => 'user',
        ]);
        $otherUser = User::factory()->create([
            'role' => 'user',
        ]);

        $order = Order::create([
            'user_id' => $owner->id,
            'total_price' => 180000,
            'status' => 'pending',
            'note' => 'Owned by another user',
        ]);

        $response = $this->actingAs($otherUser)
            ->post(route('orders.pay', $order), [
                'payment_provider' => 'midtrans',
            ]);

        $response->assertForbidden();
        $this->assertSame(0, Payment::where('order_id', $order->id)->count());
    }

    /** @param array<string, mixed> $attributes */
    private function createAddress(User $user, array $attributes = []): UserAddress
    {
        return UserAddress::factory()->for($user)->asDefault()->create([
            'label' => 'Home',
            'recipient_name' => 'Edo Wardana',
            'recipient_phone' => '+6281234567890',
            'address_line_1' => 'Jalan Sakura Nomor 10',
            'address_line_2' => 'Lantai 2',
            'city' => 'Jakarta Timur',
            'province' => 'DKI Jakarta',
            'postal_code' => '13450',
            'country_code' => 'ID',
            ...$attributes,
        ]);
    }
}
