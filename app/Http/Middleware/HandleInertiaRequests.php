<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on the first Inertia page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app' => Brand::props(),
            'auth' => [
                'admin' => fn (): ?array => $this->serializeUser(Auth::guard('admin')->user()),
                'user' => fn (): ?array => $this->serializeUser(Auth::guard('web')->user()),
            ],
            'cart' => [
                'count' => fn (): int => $this->cartCount($request),
            ],
            'flash' => [
                'error' => fn (): ?string => $this->sessionMessage($request, 'error'),
                'success' => fn (): ?string => $this->sessionMessage($request, 'success'),
            ],
            'locale' => app()->getLocale(),
        ];
    }

    private function sessionMessage(Request $request, string $key): ?string
    {
        $message = $request->session()->get($key);

        return is_string($message) ? $message : null;
    }

    private function cartCount(Request $request): int
    {
        $cart = $request->session()->get('cart', []);
        if (! is_array($cart)) {
            return 0;
        }

        return (int) collect($cart)->sum(function (mixed $quantity): int {
            if (! is_int($quantity) && ! is_numeric($quantity)) {
                return 0;
            }

            return max(0, (int) $quantity);
        });
    }

    /**
     * @param  mixed  $user
     * @return array{id: int, username: string, email: string, role: string}|null
     */
    private function serializeUser($user): ?array
    {
        if (! $user instanceof User) {
            return null;
        }

        return [
            'id' => (int) $user->getKey(),
            'username' => (string) $user->username,
            'email' => (string) $user->email,
            'role' => (string) $user->role,
        ];
    }
}
