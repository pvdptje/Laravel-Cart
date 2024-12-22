<?php namespace App\Cart\Contracts;

interface DriverInterface
{
    public function save(string $storageKey, array $data): void;
    public function get(string $storageKey): array;
}