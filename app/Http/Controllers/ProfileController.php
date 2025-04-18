<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{

    /**
     * Create a new component instance, and set the user and profile photo.
     * if the user has no profile photo, the default photo will be used.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->profilePhoto = $user->profile_photo_path
            ? asset($user->profile_photo_path)
            : asset('media/default.png');
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's profile photo.
     *
     * @param $file
     * @return void
     */
    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:3072',
        ]);

        $user = Auth::user();

        if ($request->hasFile('photo')) {
            // Ensure the photo path is correct, deleting it from the public storage
            $photoPath = str_replace('storage/', '', $user->profile_photo_path);

            // Ensures the file exists before deleting it
            if (Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
                Log::debug('File deleted successfully: ' . $photoPath);
            } else {
                Log::debug('File does not exist: ' . $photoPath);
            }

            // Saves the new profile
            $path = $request->file('photo')->store('profile_photos', 'public');

            // Updates the user
            $user->update(['profile_photo_path' => "storage/$path"]);
        }

        return back()->with('success', 'Foto de perfil actualizada correctamente.');
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
