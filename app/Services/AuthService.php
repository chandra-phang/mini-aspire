<?php

namespace App\Services;

use App\Repositories\UserRepository;

class AuthService
{
    protected $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser($data)
    { 
        if ($data->isAdmin == null) {
            $data['isAdmin'] = false;
        }

        $user = $this->userRepository->create($data);

        return $user;
    }

    public function createToken($data)
    {
        $user = $this->userRepository->findByEmail($data['email']);
        $token = $user->createToken('auth_token')->plainTextToken;

        return $token;
    }
}