<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\UpdateProfileDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\Auth\AuthTokenResource;
use App\Http\Resources\Auth\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Authentication
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Register a new UMKM account.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto    = RegisterDTO::fromRequest($request->validated());
        $result = $this->authService->register($dto);

        return $this->created(
            new AuthTokenResource($result),
            'Akun berhasil dibuat.'
        );
    }

    /**
     * Login and get API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto    = LoginDTO::fromRequest($request->validated());
        $result = $this->authService->login($dto);

        if (! $result) {
            return $this->unauthorized('Email atau password salah.');
        }

        return $this->ok(
            new AuthTokenResource($result),
            'Login berhasil.'
        );
    }

    /**
     * Get the currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->ok(
            new UserResource($request->user()),
            'Data profil.'
        );
    }

    /**
     * Update the currently authenticated user's profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $dto  = UpdateProfileDTO::fromRequest($request->validated());
        $user = $this->authService->updateProfile($request->user(), $dto);

        return $this->ok(
            new UserResource($user),
            'Profil berhasil diperbarui.'
        );
    }

    /**
     * Logout (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->noContent('Logout berhasil.');
    }
}
