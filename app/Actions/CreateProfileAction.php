<?php
namespace App\Actions;

use App\Models\User;
use App\Models\Profile;

class CreateProfileAction
{
    public function handle(User $user, array $profileData)
    {
        return Profile::create([
            'user_id' => $user->id,
            'bio' => $profileData['bio'],
            'location' => $profileData['location'],
        ]);
    }
}
