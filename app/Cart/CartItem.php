<?php namespace App\Cart;

use App\Cart\CartImageResolvers\DefaultResolver;
use App\Cart\Contracts\ImageResolverInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CartItem
 *
 * Represents an item in the shopping cart with its properties like id, name, price, quantity, tax rate, metadata, and more.
 *
 * @package App\Cart
 */
class CartItem {

    /**
     * @var string The unique identifier for the cart item.
     */
    protected string $id;

    /**
     * @var string The name of the cart item.
     */
    protected string $name;

    /**
     * @var float The price of the cart item.
     */
    protected float $price;

    /**
     * @var int The quantity of the cart item in the cart.
     */
    public int $quantity = 1;

    /**
     * @var float The tax rate applicable to the cart item.
     */
    protected float $taxRate = 0;

    /**
     * @var array Metadata associated with the cart item.
     */
    protected array $metaData = [];

    /**
     * @var string|null The image associated with the cart item.
     */
    public ?string $image;

    /**
     * @var string|null The class name of the image resolver to be used for the cart item.
     */
    protected ?string $imageResolver;

    /**
     * @var array Data model associated with the cart item.
     */
    protected array $model = [];

    /**
     * @var string A unique key generated for the cart item.
     */
    protected string $itemKey;

    /**
     * @var string The group this cart item belongs to.
     */
    protected string $group = '';

    /**
     * CartItem constructor.
     *
     * @param string $id The unique identifier for the cart item.
     * @param string $name The name of the cart item.
     * @param float $price The price of the cart item.
     * @param int $quantity The quantity of the cart item in the cart.
     * @param float $taxRate The tax rate for the cart item.
     * @param array|Model|null $model The model associated with the cart item.
     * @param array $metaData The metadata associated with the cart item.
     * @param string|null $image The image associated with the cart item.
     * @param string|null $imageResolver The image resolver class name to use for the cart item.
     * @param string $group The group this cart item belongs to.
     */
    public function __construct(
        string $id, 
        string $name, 
        float $price, 
        int $quantity = 1, 
        float $taxRate = 0, 
        Model | array | null $model = null, 
        array $metaData = [], 
        string $image = null, 
        string $imageResolver = null, 
        string $group = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->taxRate = $taxRate;
        $this->metaData = $metaData;
        $this->image = $image;
        $this->imageResolver = $imageResolver;
        $this->group = $group;

        if($model){
            $this->setModel($model);
        }

        $this->itemKey = $this->generateItemKey();
    }

    /**
     * Get the unique identifier of the cart item.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the name of the cart item.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the price of the cart item.
     *
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Get the tax rate for the cart item.
     *
     * @return float
     */
    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    /**
     * Get the group of the cart item.
     *
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * Set the group for the cart item.
     *
     * @param string $group
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * Get the image associated with the cart item.
     * Resolves the image using the specified image resolver, or the default resolver if none is provided.
     *
     * @return string
     * @throws \Exception
     */
    public function getImage(): string
    {
        if($this->imageResolver){

            $resolver = app($this->imageResolver);

            if(!$resolver instanceof ImageResolverInterface){
                throw new \Exception('Image resolver must implement ImageResolverInterface');
            }

            return $resolver->resolve($this);
        }

        return app(DefaultResolver::class)->resolve($this);
    }

    /**
     * Get the unique item key for the cart item.
     *
     * @return string
     */
    public function getItemKey(): string
    {
        return $this->itemKey;
    }

    /**
     * Get the quantity of the cart item.
     *
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Set the metadata for the cart item.
     *
     * @param array $metaData
     */
    public function setMetaData(array $metaData): void
    {
        $this->metaData = $metaData;
    }

    /**
     * Get the metadata associated with the cart item.
     *
     * @return array
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }

    /**
     * Convert the cart item to an associative array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'taxRate' => $this->taxRate,
            'metaData' => $this->metaData,
            'model' => $this->model,
            'image' => $this->image,
            'imageResolver' => $this->imageResolver,
            'group' => $this->group
        ];
    }

    /**
     * Generate a unique item key based on the cart item's id and metadata.
     *
     * @return string
     */
    protected function generateItemKey(): string
    {
        return md5($this->id . serialize($this->metaData));
    }

    /**
     * Set the model associated with the cart item.
     *
     * @param array|Model $model
     * @throws \Exception
     */
    protected function setModel(array | Model $model): void
    {
        if(is_array($model)){

            if(!isset($model['id']) || !isset($model['class'])){
                throw new \Exception('Model array must contain id and class keys');
            }

            $this->model = $model;
            return;
        }

        $this->model['id'] = $model->id;
        $this->model['class'] = get_class($model);	
    }

    /**
     * Get the model associated with the cart item, or null if none exists.
     *
     * @return Model|null
     */
    public function getModel(): Model | null
    {
        if(empty($this->model)){
            return null;
        }

        return (new $this->model['class'])->find($this->model['id']);
    }

    /**
     * Get the price of the cart item, optionally including tax.
     *
     * @param bool $ex Whether to exclude tax (default: true).
     * @return float
     */
    public function price( $ex = true )
    {
        if($ex){
            return $this->price;
        }

        return $this->price * (1 + $this->taxRate);
    }

    /**
     * Get the total price for the cart item, accounting for quantity and optionally including tax.
     *
     * @param bool $ex Whether to exclude tax (default: true).
     * @return float
     */
    public function total( $ex = true )
    {
        return $this->price($ex) * $this->quantity;
    }
}
