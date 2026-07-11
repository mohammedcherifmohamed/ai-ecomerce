<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected CustomerRepositoryInterface $customerRepository,
    ) {}

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::Customer,
            ]);

            $customer = $this->customerRepository->create([
                'user_id' => $user->id,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'zip_code' => $data['zip_code'] ?? null,
                'country' => $data['country'] ?? 'US',
            ]);

            return [
                'user' => $user->fresh(['customer']),
            ];
        });
    }

    public function login(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new \InvalidArgumentException('Invalid credentials.');
        }

        return [
            'user' => $user->fresh(['customer', 'employee']),
        ];
    }
}
