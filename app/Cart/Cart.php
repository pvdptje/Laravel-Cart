<?php namespace App\Cart;

use App\Cart\Contracts\DriverInterface;
use App\Cart\Drivers\SessionDriver;

class Cart {

    protected string $storageKey = 'cart';

    protected CartItemCollection $items;

    protected DriverInterface $driver;

    public function __construct( $storageKey = 'cart' , $driver = null )
    {
        $this->storageKey = $storageKey;

        $this->setDriver($driver);

        $this->items = $this->getCart();
    }

    protected function setDriver($driver = null): void
    {
        if($driver){
            $this->driver = $driver;
            return;
        }
        
        $this->driver = new SessionDriver();
    }

    protected function getCart(): CartItemCollection
    {
        return CartItemCollection::fromArray(
            $this->driver->get(
                $this->storageKey
            )
        );
    }

    public function add(CartItem $item): void
    {
        $itemKey = $item->getItemKey();

        $existingItem = $this->items->findByItemKey($itemKey);

        if($existingItem){
            $existingItem->quantity += $item->getQuantity();
        } else {
            $this->items->push($item);
        }

        $this->save();
    }


    public function update($itemKey, $quantity = 1, $metaData = []): void
    {
        $existingItem = $this->items->findByItemKey($itemKey);

        if($existingItem){
            $existingItem->quantity = $quantity;

            if($metaData){
                $existingItem->setMetaData($metaData);
            }
        }

        $this->save();
    }

    protected function save(): void
    {
        $this->driver->save($this->storageKey, $this->items->toArray());
    }

    public function items(): CartItemCollection
    {
        return $this->items;
    }

    public function total($ex = false): float
    {
        return $this->items->reduce(function ($total, $item) use ($ex) {
            return $total + $item->total($ex);
        }, 0);
    }

    public function totalByGroup($group, $ex = false): float
    {
        return $this->items->itemsByGroup($group)->reduce(function ($total, $item) use ($ex) {
            return $total + $item->total($ex);
        }, 0);
    }
}