<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\Souvenir;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('payments')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('place_reviews')->truncate();
        DB::table('souvenirs')->truncate();
        DB::table('places')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@japantravel.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $users = collect(DemoData::users())->map(function ($user) {
            return User::create([
                'username' => $user['username'],
                'email' => $user['email'],
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
        });

        $places = DemoData::places();

        foreach ($places as $placeData) {
            Place::create([
                'name' => [
                    'id' => $placeData['name_id'],
                    'en' => $placeData['name_en'],
                ],
                'slug' => Str::slug($placeData['name_en']) . '-' . Str::random(6),
                'description' => [
                    'id' => $placeData['description_id'],
                    'en' => $placeData['description_en'],
                ],
                'image' => null,
                'address' => $placeData['address'],
                'facilities' => $placeData['facilities'],
                'open_days' => $placeData['open_days'],
                'open_hours' => $placeData['open_hours'],
                'created_by' => $admin->id,
            ]);
        }

        foreach (DemoData::souvenirs() as $souvenirData) {
            Souvenir::create([
                'name' => [
                    'id' => $souvenirData['name_id'],
                    'en' => $souvenirData['name_en'],
                ],
                'description' => [
                    'id' => $souvenirData['description_id'],
                    'en' => $souvenirData['description_en'],
                ],
                'price' => $souvenirData['price'],
                'stock' => $souvenirData['stock'],
                'image' => null,
            ]);
        }

        $reviewTemplates = DemoData::reviewTemplates();

        mt_srand(2026);
        $placesCollection = Place::all();
        $reviewerPool = $users->values();
        $templateCount = count($reviewTemplates);

        foreach ($placesCollection as $place) {
            $reviewCount = mt_rand(2, 5);
            for ($i = 0; $i < $reviewCount; $i++) {
                $reviewer = $reviewerPool[mt_rand(0, $reviewerPool->count() - 1)];
                $template = $reviewTemplates[mt_rand(0, $templateCount - 1)];
                $date = now()->subDays(mt_rand(1, 120));

                $review = PlaceReview::create([
                    'place_id' => $place->id,
                    'user_id' => $reviewer->id,
                    'rating' => $template['rating'],
                    'comment' => $template['comment'],
                ]);

                $review->forceFill([
                    'created_at' => $date,
                    'updated_at' => $date,
                ])->save();
            }
        }

        mt_srand(2027);
        $souvenirs = Souvenir::all()->values();
        $orderStatuses = ['pending', 'processing', 'completed'];
        $pendingPaymentStatuses = ['pending', 'expired', 'failed'];
        for ($i = 0; $i < 18; $i++) {
            $user = $users->values()[mt_rand(0, $users->count() - 1)];
            $orderDate = now()->subDays(mt_rand(1, 120));
            $status = $orderStatuses[mt_rand(0, count($orderStatuses) - 1)];

            $itemsCount = mt_rand(1, 3);
            $itemIndexes = [];
            while (count($itemIndexes) < $itemsCount) {
                $itemIndexes[mt_rand(0, $souvenirs->count() - 1)] = true;
            }

            $selectedItems = $souvenirs->only(array_keys($itemIndexes));

            $total = 0;
            $itemsData = [];

            foreach ($selectedItems as $item) {
                $qty = mt_rand(1, 3);
                if ($item->stock < $qty) {
                    continue;
                }

                $total += $item->price * $qty;
                $itemsData[] = [
                    'souvenir' => $item,
                    'qty' => $qty,
                    'price' => $item->price,
                ];
            }

            if (empty($itemsData)) {
                continue;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $total,
                'status' => $status,
                'note' => 'Pesanan demo untuk kebutuhan portofolio.',
            ]);

            $order->forceFill([
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ])->save();

            foreach ($itemsData as $data) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'souvenir_id' => $data['souvenir']->id,
                    'quantity' => $data['qty'],
                    'price' => $data['price'],
                    'product_name' => $data['souvenir']->name,
                    'product_price' => $data['price'],
                    'product_image' => $data['souvenir']->image,
                ]);

                $data['souvenir']->decrement('stock', $data['qty']);
            }

            $provider = ['midtrans', 'paypal'][mt_rand(0, 1)];
            $paymentStatus = $status === 'pending'
                ? $pendingPaymentStatuses[mt_rand(0, count($pendingPaymentStatuses) - 1)]
                : 'paid';

            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => $provider,
                'provider_ref' => 'ORD-' . $order->id . '-' . Str::uuid(),
                'status' => $paymentStatus,
                'amount' => $total,
                'currency' => 'IDR',
                'payload_json' => [
                    'seeded' => true,
                ],
                'paid_at' => $paymentStatus === 'paid' ? $orderDate->copy()->addHours(rand(1, 24)) : null,
            ]);

            $payment->forceFill([
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ])->save();

            if ($paymentStatus === 'paid' && $status === 'pending') {
                $order->update(['status' => 'processing']);
            }
        }
    }
}
