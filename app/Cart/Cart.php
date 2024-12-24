<?php namespace App\Cart;

use App\Cart\Contracts\DriverInterface;
use App\Cart\Drivers\SessionDriver;

/**
 * Class Cart
 *
 * Manages the shopping cart, including adding, updating, and calculating totals for items.
 *
 * @package App\Cart
 */
class Cart {

    /**
     * The key used for storing the cart in the driver (e.g., session or database).
     *
     * @var string
     */
    protected string $storageKey;

    /**
     * The collection of cart items.
     *
     * @var CartItemCollection
     */
    protected CartItemCollection $items;

    /**
     * The driver responsible for storing and retrieving the cart data.
     *
     * @var DriverInterface
     */
    protected DriverInterface $driver;


    /**
     * Cart constructor.
     *
     * @param string $storageKey The key used to store the cart data (default is 'cart').
     * @param DriverInterface|null $driver The driver used for storage (default is SessionDriver).
     */
    public function __construct($storageKey = 'cart', $driver = null)
    {
        $this->storageKey = $storageKey;
        $this->setDriver($driver);
        $this->items = $this->getCart();
    }

    /**
     * Sets the driver for storing the cart data.
     *
     * @param DriverInterface|null $driver The driver to be used (default is null, in which case SessionDriver is used).
     * @return void
     */
    protected function setDriver($driver = null): void
    {
        if ($driver) {
            $this->driver = $driver;
            return;
        }
        
        $this->driver = new SessionDriver();
    }

    /**
     * Retrieves the cart data from the driver and returns it as a CartItemCollection.
     *
     * @return CartItemCollection
     */
    protected function getCart(): CartItemCollection
    {
        return CartItemCollection::fromArray(
            $this->driver->get(
                $this->storageKey
            )
        );
    }

    /**
     * Adds an item to the cart.
     *
     * If the item already exists in the cart, its quantity is updated.
     * Otherwise, the item is added as a new entry.
     *
     * @param CartItem $item The cart item to be added.
     * @return void
     */
    public function add(CartItem $item): void
    {
        $itemKey = $item->getItemKey();

        $existingItem = $this->items->findByItemKey($itemKey);

        if ($existingItem) {
            $existingItem->quantity += $item->getQuantity();
        } else {
            $this->items->push($item);
        }

        $this->save();
    }

    /**
     * Updates the quantity and metadata of an existing item in the cart.
     *
     * @param string $itemKey The key of the item to be updated.
     * @param int $quantity The new quantity of the item.
     * @param array $metaData The additional metadata for the item (optional).
     * @return void
     */
    public function update($itemKey, $quantity = 1, $metaData = []): void
    {
        $existingItem = $this->items->findByItemKey($itemKey);

        if ($existingItem) {
            $existingItem->quantity = $quantity;

            if ($metaData) {
                $existingItem->setMetaData($metaData);
            }
        }

        $this->save();
    }

    /**
     * Saves the current cart data to the driver.
     *
     * @param bool $withCallbacks Whether to run any callbacks associated with the cart items (default is true).
     * @return void
     */
    public function save( $withCallbacks = true ): void
    {
        if($withCallbacks){
            $this->runCallbacks();
        }

        $this->driver->save($this->storageKey, $this->items->toArray());
    }

    /**
     * Runs any callbacks associated with the cart items.
     *
     * @return void
     */
    protected function runCallbacks(): void
    {
     
        $this->items->filter(function ($item) {
            return $item->getCallback() !== '';
        })->each(function ($item) {
            $callback = $item->getCallback();
               
            if (is_string($callback) && strpos($callback, '::') !== false) {
                list($class, $method) = explode('::', $callback);
    
                if (class_exists($class) && method_exists($class, $method)) {
                    call_user_func([$class, $method], $item, $this);
                } else {
                    throw new \Exception("Callback {$callback} is not callable.");
                }
            } else {
                throw new \Exception("Invalid callback format: {$callback}");
            }
        });
    }

    /**
     * Retrieves the collection of items in the cart.
     *
     * @return CartItemCollection
     */
    public function items($group = null): CartItemCollection
    {
        if($group) {
            return $this->items->itemsByGroup($group);
        }

        return $this->items;
    }

    /**
     * Calculates the total price of all items in the cart.
     *
     * @param bool $ex Whether to exclude taxes from the total (default is false).
     * @return float The total price.
     */
    public function total($ex = false): float
    {
        return $this->items->reduce(function ($total, $item) use ($ex) {
            return $total + $item->total($ex);
        }, 0);
    }

    /**
     * Calculates the total price of items in the cart for a specific group.
     *
     * @param string $group The group of items to total.
     * @param bool $ex Whether to exclude taxes from the total (default is false).
     * @return float The total price for the group.
     */
    public function totalByGroup($group, $ex = false): float
    {
        return $this->items->itemsByGroup($group)->reduce(function ($total, $item) use ($ex) {
            return $total + $item->total($ex);
        }, 0);
    }

    public function clear(): void
    {
        $this->items = new CartItemCollection();
        $this->save(false);
    }

    public function totalItems(): int
    {
        return $this->items->reduce(function ($total, $item) {
            return $total + $item->getQuantity();
        }, 0);
    }

    public function totalItemsByGroup($group): int
    {
        return $this->items->itemsByGroup($group)->reduce(function ($total, $item) {
            return $total + $item->getQuantity();
        }, 0);
    }

    public function remove($itemKey): void
    {
        $this->items = $this->items->filter(function ($item) use ($itemKey) {
            return $item->getItemKey() !== $itemKey;
        });

        $this->save();
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }
}
