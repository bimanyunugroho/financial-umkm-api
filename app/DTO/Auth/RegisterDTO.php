<?php

namespace App\DTO\Auth;

final class RegisterDTO
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $email,
        public readonly string  $password,
        public readonly string  $businessName,
        public readonly string  $businessType,
        public readonly ?string $phone   = null,
        public readonly ?string $address = null,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            name:         $validated['name'],
            email:        $validated['email'],
            password:     $validated['password'],
            businessName: $validated['business_name'],
            businessType: $validated['business_type'],
            phone:        $validated['phone']    ?? null,
            address:      $validated['address']  ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'          => $this->name,
            'email'         => $this->email,
            'password'      => $this->password,
            'business_name' => $this->businessName,
            'business_type' => $this->businessType,
            'phone'         => $this->phone,
            'address'       => $this->address,
        ];
    }
}
