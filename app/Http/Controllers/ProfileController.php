<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        if (in_array($user->role, ['customer', 'courier'])) {
            return view('mobile.profile', [
                'user' => $user,
            ]);
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $newEmail = $request->input('email');

        // Check if email is changing and if it's verified via OTP
        if ($newEmail !== $user->email) {
            $otpData = session('profile_otp');
            if (!session('profile_otp_verified') || !$otpData || $otpData['type'] !== 'email' || $otpData['email'] !== $newEmail) {
                return back()->with('error', 'Verifikasi OTP diperlukan untuk mengubah email.');
            }
            session()->forget(['profile_otp', 'profile_otp_verified']);
        }

        $user->fill($request->safe()->except('photo'));

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profiles', 'public');
            $user->photo = $path;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Sync with customer profile if applicable
        if ($request->user()->role === 'customer' && $request->user()->customer) {
            $request->user()->customer->update([
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'phone' => $request->user()->phone,
                'address' => $request->user()->address,
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
