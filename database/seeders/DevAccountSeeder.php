<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Souvenir;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DevAccountSeeder extends Seeder
{
    private const USER_EMAIL = 'user.demo@japantravel.test';

    private const ADMIN_EMAIL = 'admin.demo@japantravel.test';

    public function run(): void
    {
        $password = Hash::make('Password123!');

        $user = User::updateOrCreate(
            ['email' => self::USER_EMAIL],
            [
                'username' => 'dev_user_demo',
                'password' => $password,
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'username' => 'dev_admin_demo',
                'password' => $password,
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $souvenirs = Souvenir::query()->oldest('id')->take(3)->get();

        if ($souvenirs->isEmpty()) {
            $this->command->warn('No souvenirs found. Run the main demo/database seeder first before creating demo orders.');

            return;
        }

        Order::query()
            ->where('user_id', $user->id)
            ->where('note', 'Dev visual review order')
            ->get()
            ->each(fn (Order $order) => $order->delete());

        $samples = [
            [
                'status' => 'pending',
                'payment_status' => 'pending',
                'provider' => 'midtrans',
                'created_at' => now()->subDays(1),
            ],
            [
                'status' => 'processing',
                'payment_status' => 'paid',
                'provider' => 'paypal',
                'created_at' => now()->subDays(8),
            ],
            [
                'status' => 'completed',
                'payment_status' => 'paid',
                'provider' => 'midtrans',
                'created_at' => now()->subDays(21),
            ],
            [
                'status' => 'cancelled',
                'payment_status' => 'expired',
                'provider' => 'paypal',
                'created_at' => now()->subDays(34),
            ],
        ];

        foreach ($samples as $index => $sample) {
            $items = $this->buildItems($souvenirs, $index);
            $total = collect($items)->sum(fn (array $item): float => (float) $item['price'] * (int) $item['quantity']);

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => $sample['status'],
                'note' => 'Dev visual review order',
            ]);

            $order->forceFill([
                'created_at' => $sample['created_at'],
                'updated_at' => $sample['created_at'],
            ])->save();

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'souvenir_id' => $item['souvenir']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'product_name' => $item['souvenir']->name,
                    'product_price' => $item['price'],
                    'product_image' => $item['souvenir']->image,
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'provider' => $sample['provider'],
                'provider_ref' => 'DEV-'.$order->id.'-'.Str::uuid(),
                'status' => $sample['payment_status'],
                'amount' => $total,
                'currency' => 'IDR',
                'payload_json' => [
                    'seeded' => true,
                    'purpose' => 'local visual review only',
                ],
                'paid_at' => $sample['payment_status'] === 'paid' ? $sample['created_at']->copy()->addHours(2) : null,
                'created_at' => $sample['created_at'],
                'updated_at' => $sample['created_at'],
            ]);
        }

        $this->command->info('Dev accounts created:');
        $this->command->line('User: '.self::USER_EMAIL.' / Password123!');
        $this->command->line('Admin: '.self::ADMIN_EMAIL.' / Password123!');
        $this->command->info('Demo orders created for the user account: '.count($samples));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Souvenir>  $souvenirs
     * @return array<int, array{souvenir: Souvenir, quantity: int, price: mixed}>
     */
    private function buildItems($souvenirs, int $offset): array
    {
        return $souvenirs
            ->values()
            ->take(2)
            ->map(function (Souvenir $souvenir, int $index) use ($offset): array {
                return [
                    'souvenir' => $souvenir,
                    'quantity' => (($offset + $index) % 2) + 1,
                    'price' => $souvenir->price,
                ];
            })
            ->all();
    }
}
