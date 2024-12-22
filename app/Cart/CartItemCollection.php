<?php namespace App\Cart;

use Illuminate\Support\Collection;

class CartItemCollection extends Collection {

    public static function fromArray($items)
    {
        $cartItems = new static;

        foreach ($items as $item) {

            $cartItems->push(new CartItem(
                $item['id'],
                $item['name'],
                $item['price'],
                $item['quantity'] ?? 1,
                $item['taxRate'] ?? 0,
                $item['model'] ?? null,
                $item['metaData'] ?? [], 
                $item['image'] ?? null,
                $item['imageResolver'] ?? null,
                $item['group'] ?? ''
            ));
        }

        return $cartItems;
    }

    public function toArray()
    {
        return $this->map(function ($item) {
            return $item->toArray();
        })->all();
    }

    public function findByItemKey($itemKey)
    {
        return $this->first(function ($cartItem) use ($itemKey) {
            return $cartItem->getItemKey() === $itemKey;
        });
    }

    public function itemsByGroup($group)
    {
        return $this->filter(function ($cartItem) use ($group) {
            return $cartItem->group === $group;
        });
    }
}