<?php

namespace App\Interfaces\Auth;

use App\DTOs\Auth\LoginData;
use App\DTOs\Auth\RegisterData;
use App\Models\User;

interface AuthServiceInterface
{
    /**
     * @return array{user: User, token: string}
     */
    public function register(RegisterData $data): array;

    /**
     * @return array{user: User, token: string}
     */
    public function login(LoginData $data): array;

    public function logout(User $user): void;
}
