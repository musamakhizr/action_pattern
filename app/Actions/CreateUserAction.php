<?php
namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Actions\CreateProfileAction;

class CreateUserAction
{
    public function handle(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);

            $profileAction = new CreateProfileAction();
            $profile = $profileAction->handle($user, $data['profile_data']);

            return [
                'user' => $user,
                'profile' => $profile,
            ];
        });
    }
}
