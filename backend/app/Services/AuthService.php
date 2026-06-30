<?php

namespace App\Services;

use App\Events\UserRegistered;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AuthService
{
    public function __construct(private readonly CacheVersionService $cache) {}

    public function register(array $attributes): array
    {
        $payload = DB::transaction(function () use ($attributes): array {
            $role = Role::query()->where('slug', 'customer')->first();

            if (! $role) {
                throw new RuntimeException('The customer role has not been configured. Run the database seeders.');
            }

            $user = User::query()->create([
                ...Arr::only($attributes, ['name', 'email', 'phone', 'password']),
                'role_id' => $role->id,
                'status' => 'active',
            ]);

            $user->cart()->create();
            $user->wishlist()->create();

            return $this->authenticationPayload($user, $attributes['device_name']);
        });

        event(new UserRegistered($payload['user']));
        $this->cache->bump('dashboard');

        return $payload;
    }

    public function login(array $credentials): array
    {
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('The provided credentials are incorrect.');
        }

        if ($user->status !== 'active') {
            throw new AuthenticationException('This account is not active.');
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->authenticationPayload($user, $credentials['device_name']);
    }

    private function authenticationPayload(User $user, string $deviceName): array
    {
        return [
            'user' => $user->load('role.permissions'),
            'token' => $user->createToken($deviceName)->plainTextToken,
        ];
    }
}
