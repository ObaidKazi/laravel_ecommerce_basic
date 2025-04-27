<?php
namespace App\Repositories\Interfaces;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function register(array $data): User;
    public function login(array $credentials): ?string;
}
