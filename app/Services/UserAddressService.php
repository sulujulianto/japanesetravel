<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;

class UserAddressService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $user, array $attributes): UserAddress
    {
        return DB::transaction(function () use ($user, $attributes): UserAddress {
            $lockedUser = $this->lockUser($user);
            $makeDefault = ! $lockedUser->addresses()->where('is_default', true)->exists()
                || (bool) ($attributes['is_default'] ?? false);

            if ($makeDefault) {
                $lockedUser->addresses()->update(['is_default' => false]);
            }

            $attributes['is_default'] = $makeDefault;

            return $lockedUser->addresses()->create($attributes);
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, int $addressId, array $attributes): UserAddress
    {
        return DB::transaction(function () use ($user, $addressId, $attributes): UserAddress {
            $lockedUser = $this->lockUser($user);
            $lockedAddress = $this->ownedAddress($lockedUser, $addressId);
            $makeDefault = array_key_exists('is_default', $attributes)
                ? (bool) $attributes['is_default']
                : $lockedAddress->is_default;

            if ($makeDefault) {
                $lockedUser->addresses()
                    ->whereKeyNot($lockedAddress->getKey())
                    ->update(['is_default' => false]);
            } elseif ($lockedAddress->is_default) {
                $lockedUser->addresses()
                    ->whereKeyNot($lockedAddress->getKey())
                    ->update(['is_default' => false]);

                $replacement = $lockedUser->addresses()
                    ->whereKeyNot($lockedAddress->getKey())
                    ->orderBy('id')
                    ->first();

                if ($replacement instanceof UserAddress) {
                    $replacement->update(['is_default' => true]);
                } else {
                    $makeDefault = true;
                }
            } elseif (! $lockedUser->addresses()->where('is_default', true)->exists()) {
                $makeDefault = true;
            }

            $attributes['is_default'] = $makeDefault;
            $lockedAddress->update($attributes);

            return $lockedAddress->refresh();
        }, 3);
    }

    public function delete(User $user, int $addressId): void
    {
        DB::transaction(function () use ($user, $addressId): void {
            $lockedUser = $this->lockUser($user);
            $lockedAddress = $this->ownedAddress($lockedUser, $addressId);
            $wasDefault = $lockedAddress->is_default;

            $lockedAddress->delete();

            if ($wasDefault || ! $lockedUser->addresses()->where('is_default', true)->exists()) {
                $lockedUser->addresses()->update(['is_default' => false]);
                $replacement = $lockedUser->addresses()->orderBy('id')->first();
                $replacement?->update(['is_default' => true]);
            }
        }, 3);
    }

    public function makeDefault(User $user, int $addressId): UserAddress
    {
        return DB::transaction(function () use ($user, $addressId): UserAddress {
            $lockedUser = $this->lockUser($user);
            $lockedAddress = $this->ownedAddress($lockedUser, $addressId);

            $lockedUser->addresses()
                ->whereKeyNot($lockedAddress->getKey())
                ->update(['is_default' => false]);

            $lockedAddress->update(['is_default' => true]);

            return $lockedAddress->refresh();
        }, 3);
    }

    private function lockUser(User $user): User
    {
        return User::query()->lockForUpdate()->findOrFail($user->getKey());
    }

    private function ownedAddress(User $user, int $addressId): UserAddress
    {
        return $user->addresses()->whereKey($addressId)->firstOrFail();
    }
}
