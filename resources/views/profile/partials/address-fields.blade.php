@php
    $editingAddress = $address instanceof \App\Models\UserAddress;
    $addressKey = $editingAddress ? (string) $address->getKey() : 'new';
    $useOldInput = $editingAddress
        ? (string) old('address_id') === $addressKey
        : old('address_id') === null;
    $fieldValue = static function (string $field, mixed $fallback = null) use ($useOldInput): mixed {
        return $useOldInput ? old($field, $fallback) : $fallback;
    };
@endphp

@if ($editingAddress)
    <input type="hidden" name="address_id" value="{{ $address->getKey() }}">
@endif

<input type="hidden" name="country_code" value="ID">

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <x-input-label :for="'address-'.$addressKey.'-label'" :value="__('Label alamat')" />
        <x-text-input :id="'address-'.$addressKey.'-label'" name="label" type="text" class="mt-2 block w-full" :value="$fieldValue('label', $address?->label)" placeholder="{{ __('Contoh: Rumah') }}" autocomplete="off" maxlength="50" required />
        @if ($useOldInput)
            <x-input-error class="mt-2" :messages="$errors->get('label')" />
        @endif
    </div>

    <div>
        <x-input-label :for="'address-'.$addressKey.'-recipient-name'" :value="__('Nama penerima')" />
        <x-text-input :id="'address-'.$addressKey.'-recipient-name'" name="recipient_name" type="text" class="mt-2 block w-full" :value="$fieldValue('recipient_name', $address?->recipient_name)" autocomplete="name" maxlength="100" required />
        @if ($useOldInput)
            <x-input-error class="mt-2" :messages="$errors->get('recipient_name')" />
        @endif
    </div>

    <div class="sm:col-span-2">
        <x-input-label :for="'address-'.$addressKey.'-recipient-phone'" :value="__('Nomor telepon penerima')" />
        <x-text-input :id="'address-'.$addressKey.'-recipient-phone'" name="recipient_phone" type="tel" class="mt-2 block w-full" :value="$fieldValue('recipient_phone', $address?->recipient_phone)" autocomplete="tel" placeholder="+62 812-3456-7890" maxlength="30" required />
        @if ($useOldInput)
            <x-input-error class="mt-2" :messages="$errors->get('recipient_phone')" />
        @endif
    </div>

    <div class="sm:col-span-2">
        <x-input-label :for="'address-'.$addressKey.'-line-1'" :value="__('Alamat baris 1')" />
        <textarea id="address-{{ $addressKey }}-line-1" name="address_line_1" rows="3" class="auth-input mt-2 block w-full px-4 py-2.5 text-sm" autocomplete="street-address" maxlength="255" required>{{ $fieldValue('address_line_1', $address?->address_line_1) }}</textarea>
        @if ($useOldInput)
            <x-input-error class="mt-2" :messages="$errors->get('address_line_1')" />
        @endif
    </div>

    <div class="sm:col-span-2">
        <x-input-label :for="'address-'.$addressKey.'-line-2'" :value="__('Alamat baris 2 (opsional)')" />
        <x-text-input :id="'address-'.$addressKey.'-line-2'" name="address_line_2" type="text" class="mt-2 block w-full" :value="$fieldValue('address_line_2', $address?->address_line_2)" autocomplete="address-line2" maxlength="255" />
        @if ($useOldInput)
            <x-input-error class="mt-2" :messages="$errors->get('address_line_2')" />
        @endif
    </div>

    <div>
        <x-input-label :for="'address-'.$addressKey.'-city'" :value="__('Kota atau kabupaten')" />
        <x-text-input :id="'address-'.$addressKey.'-city'" name="city" type="text" class="mt-2 block w-full" :value="$fieldValue('city', $address?->city)" autocomplete="address-level2" maxlength="100" required />
        @if ($useOldInput)
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        @endif
    </div>

    <div>
        <x-input-label :for="'address-'.$addressKey.'-province'" :value="__('Provinsi')" />
        <x-text-input :id="'address-'.$addressKey.'-province'" name="province" type="text" class="mt-2 block w-full" :value="$fieldValue('province', $address?->province)" autocomplete="address-level1" maxlength="100" required />
        @if ($useOldInput)
            <x-input-error class="mt-2" :messages="$errors->get('province')" />
        @endif
    </div>

    <div>
        <x-input-label :for="'address-'.$addressKey.'-postal-code'" :value="__('Kode pos')" />
        <x-text-input :id="'address-'.$addressKey.'-postal-code'" name="postal_code" type="text" inputmode="numeric" class="mt-2 block w-full" :value="$fieldValue('postal_code', $address?->postal_code)" autocomplete="postal-code" maxlength="20" required />
        @if ($useOldInput)
            <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
        @endif
    </div>

    <div>
        <x-input-label :for="'address-'.$addressKey.'-country'" :value="__('Negara tujuan')" />
        <x-text-input :id="'address-'.$addressKey.'-country'" type="text" class="mt-2 block w-full" :value="__('Indonesia')" disabled />
        @if ($useOldInput)
            <x-input-error class="mt-2" :messages="$errors->get('country_code')" />
        @endif
    </div>
</div>
