<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('web')->check();
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        $presence = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'label' => [$presence, 'string', 'max:50'],
            'recipient_name' => [$presence, 'string', 'max:100'],
            'recipient_phone' => [$presence, 'string', 'max:30', 'regex:/^\+?[0-9][0-9 .()\-]{6,29}$/'],
            'address_line_1' => [$presence, 'string', 'max:255'],
            'address_line_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => [$presence, 'string', 'max:100'],
            'province' => [$presence, 'string', 'max:100'],
            'postal_code' => [$presence, 'string', 'max:20'],
            'country_code' => [$presence, 'string', 'size:2', 'in:ID'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
