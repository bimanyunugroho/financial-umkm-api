<?php

namespace App\DTO\Auth;

final class UpdateProfileDTO
{
    public function __construct(
        public readonly ?string $name         = null,
        public readonly ?string $email        = null,
        public readonly ?string $password     = null,
        public readonly ?string $businessName = null,
        public readonly ?string $businessType = null,
        public readonly ?string $phone        = null,
        public readonly ?string $address      = null,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            name:         $validated['name']          ?? null,
            email:        $validated['email']         ?? null,
            password:     $validated['password']      ?? null,
            businessName: $validated['business_name'] ?? null,
            businessType: $validated['business_type'] ?? null,
            phone:        $validated['phone']         ?? null,
            address:      $validated['address']       ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name'          => $this->name,
            'email'         => $this->email,
            'password'      => $this->password,
            'business_name' => $this->businessName,
            'business_type' => $this->businessType,
            'phone'         => $this->phone,
            'address'       => $this->address,
        ], fn ($v) => $v !== null);
    }
}
