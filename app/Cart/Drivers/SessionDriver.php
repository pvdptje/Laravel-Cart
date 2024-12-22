<?php namespace App\Cart\Drivers;

class SessionDriver implements \App\Cart\Contracts\DriverInterface
{
    public function save(string $storageKey, array $data): void
    {
        session([$storageKey => $data]);
    }

    public function get(string $storageKey): array
    {
        return session($storageKey, []);
    }
}