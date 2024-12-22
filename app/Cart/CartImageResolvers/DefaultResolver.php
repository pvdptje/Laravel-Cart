<?php namespace App\Cart\CartImageResolvers;

use App\Cart\CartItem;

class DefaultResolver implements \App\Cart\Contracts\ImageResolverInterface {

    public function resolve(CartItem $cartItem): string
    {
        return $cartItem->image ?? '';
    }

}