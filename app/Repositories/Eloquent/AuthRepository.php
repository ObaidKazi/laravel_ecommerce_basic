<?php
namespace App\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Interfaces\AuthRepositoryInterface;

class AuthRepository implements AuthRepositoryInterface
{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->assignRole('customer'); // if using spatie/roles
        return $user;
    }

    public function login(array $credentials): ?string
    {
        if (!Auth::attempt($credentials)) {
            return null;
        }
        return Auth::user()->createToken('api')->plainTextToken;
    }
}
