<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user('web');

        return view('profile.edit', [
            'addresses' => $user->addresses()
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get(),
            'profile' => $user->profile()->first(),
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user('web');
        $validated = $request->validated();
        $accountAttributes = Arr::only($validated, ['username', 'email']);
        $profileAttributes = Arr::only($validated, ['full_name', 'phone', 'preferred_locale']);

        DB::transaction(function () use ($user, $accountAttributes, $profileAttributes): void {
            $user->fill($accountAttributes);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            if ($profileAttributes !== []) {
                $user->profile()->updateOrCreate([], $profileAttributes);
            }
        });

        $response = Redirect::route('profile.edit')->with('status', 'profile-updated');
        $preferredLocale = $profileAttributes['preferred_locale'] ?? null;

        if (is_string($preferredLocale)) {
            $response->withCookie(cookie('locale', $preferredLocale, 60 * 24 * 365));
        }

        return $response;
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        /** @var User $user */
        $user = $request->user('web');

        Auth::guard('web')->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
