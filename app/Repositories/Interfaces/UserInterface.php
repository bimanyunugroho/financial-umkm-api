<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserInterface
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): User;
}
