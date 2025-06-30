<?php
namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUserProfile
{
    public function handle(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update($data);
            return $user;
        });
    }
}
