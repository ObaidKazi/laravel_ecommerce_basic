<?php
namespace App\Services;

use App\Repositories\Interfaces\AuthRepositoryInterface;

class AuthService
{
    protected AuthRepositoryInterface $authRepo;

    public function __construct(AuthRepositoryInterface $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function register(array $data)
    {
        $user = $this->authRepo->register($data);
        return $user->createToken('api')->plainTextToken;
    }


    public function login(array $credentials)
    {
        return $this->authRepo->login($credentials);
    }
}
