<?php

namespace App\Services\Auth;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\UpdateProfileDTO;
use App\Models\User;
use App\Repositories\Interfaces\UserInterface;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly UserInterface $userRepo,
    ) {}

    public function register(RegisterDTO $dto): array
    {
        $user = $this->userRepo->create($dto->toArray());

        $token = $this->issueToken($user);

        return compact('user', 'token');
    }

    public function login(LoginDTO $dto): ?array
    {
        $user = $this->userRepo->findByEmail($dto->email);

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            return null;
        }

        // One active session per user — revoke all existing tokens
        $user->tokens()->delete();

        $token = $this->issueToken($user);

        return compact('user', 'token');
    }

    public function updateProfile(User $user, UpdateProfileDTO $dto): User
    {
        return $this->userRepo->update($user, $dto->toArray());
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    private function issueToken(User $user): array
    {
        $expiration = config('sanctum.expiration', 10080); // minutes

        $tokenResult = $user->createToken(
            'api-token',
            ['*'],
            now()->addMinutes($expiration),
        );

        return [
            'plain_text' => $tokenResult->plainTextToken,
            'expires_at' => $tokenResult->accessToken->expires_at?->toIso8601String(),
        ];
    }
}
