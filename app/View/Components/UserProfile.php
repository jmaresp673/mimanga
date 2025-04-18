<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserProfile extends Component
{
    public $user;
    public $profilePhoto;

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
            ? asset("$user->profile_photo_path")
            : asset('storage/profile_photos/default.png');
    }


    public function render()
    {
        return view('components.user-profile');
    }
}

