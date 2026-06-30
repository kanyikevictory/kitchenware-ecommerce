<?php

namespace App\Services;

use Closure;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;

class CacheVersionService
{
    public function remember(string $scope, string $key, DateTimeInterface|int $ttl, Closure $callback): mixed
    {
        return Cache::remember($this->key($scope, $key), $ttl, $callback);
    }

    public function bump(string $scope): void
    {
        $versionKey = $this->versionKey($scope);
        Cache::add($versionKey, 1, now()->addYears(10));
        Cache::increment($versionKey);
    }

    private function key(string $scope, string $key): string
    {
        return "{$scope}:v{$this->version($scope)}:{$key}";
    }

    private function version(string $scope): int
    {
        return (int) Cache::get($this->versionKey($scope), 1);
    }

    private function versionKey(string $scope): string
    {
        return "cache-version:{$scope}";
    }
}
