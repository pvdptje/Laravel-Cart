<?php namespace App\Cart\Contracts;

use App\Cart\CartItem;

interface ImageResolverInterface
{
    public function resolve(CartItem $cartItem): string;
}