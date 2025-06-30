<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Actions\CreateUserAction;
use App\Actions\UpdateUserProfile;
use App\Http\Requests\CreateUserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request)
    {
        $createUserAction = new CreateUserAction();
        $result = $createUserAction->handle($request->validated());

        return response()->json([
            'user' => $result['user'],
            'profile' => $result['profile'],
            'message' => 'User and profile created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user, UpdateUserProfile $updateUserProfile)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'bio' => 'nullable',
        ]);

        $updatedUser = $updateUserProfile->handle($user, $data);

        return response()->json(['user' => $updatedUser, 'message' => 'Profile updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
