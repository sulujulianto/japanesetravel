<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressRequest;
use App\Models\User;
use App\Services\UserAddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class UserAddressController extends Controller
{
    public function __construct(private readonly UserAddressService $addresses) {}

    public function store(UserAddressRequest $request): RedirectResponse
    {
        $this->addresses->create($this->user($request), $request->validated());

        return $this->redirectToAddresses('address-created');
    }

    public function update(UserAddressRequest $request, int $address): RedirectResponse
    {
        $this->addresses->update($this->user($request), $address, $request->validated());

        return $this->redirectToAddresses('address-updated');
    }

    public function destroy(Request $request, int $address): RedirectResponse
    {
        $this->addresses->delete($this->user($request), $address);

        return $this->redirectToAddresses('address-deleted');
    }

    public function makeDefault(Request $request, int $address): RedirectResponse
    {
        $this->addresses->makeDefault($this->user($request), $address);

        return $this->redirectToAddresses('address-defaulted');
    }

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user('web');

        return $user;
    }

    private function redirectToAddresses(string $status): RedirectResponse
    {
        return Redirect::route('profile.edit')
            ->withFragment('addresses')
            ->with('status', $status);
    }
}
