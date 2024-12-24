<?php namespace App\Cart;

use Illuminate\Support\Collection;

/**
 * Class CartItemCollection
 *
 * A collection class that manages a list of cart items, extending Laravel's base Collection class.
 *
 * @package App\Cart
 */
class CartItemCollection extends Collection {

    /**
     * Creates a new CartItemCollection instance from an array of items.
     *
     * Iterates through the provided array of items, converting each item to a CartItem object.
     *
     * @param array $items The array of items to be converted to CartItem objects.
     * @return CartItemCollection A collection of CartItem objects.
     */
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
                $item['group'] ?? '',
                $item['callback'] ?? ''
            ));
        }

        return $cartItems;
    }

    /**
     * Converts the CartItemCollection to an array of arrays, where each item is represented as an array.
     *
     * @return array The array representation of the CartItemCollection.
     */
    public function toArray()
    {
        return $this->map(function ($item) {
            return $item->toArray();
        })->all();
    }

    /**
     * Finds and returns a CartItem by its unique item key.
     *
     * @param string $itemKey The unique key of the item to search for.
     * @return CartItem|null The matching CartItem or null if not found.
     */
    public function findByItemKey($itemKey)
    {
        return $this->first(function ($cartItem) use ($itemKey) {
            return $cartItem->getItemKey() === $itemKey;
        });
    }

    /**
     * Filters the CartItemCollection by a specific group.
     *
     * @param string $group The group to filter the cart items by.
     * @return CartItemCollection A new collection containing only the items from the specified group.
     */
    public function itemsByGroup($group)
    {
        return $this->filter(function ($cartItem) use ($group) {
            return $cartItem->getGroup() === $group;
        });
    }
}
