<?php namespace App\Cart;

use App\Cart\CartImageResolvers\DefaultResolver;
use App\Cart\Contracts\ImageResolverInterface;
use Illuminate\Database\Eloquent\Model;

class CartItem {
    
    protected string $id;
    protected string $name;
    protected float $price;
    public int $quantity = 1;
    protected float $taxRate = 0;
    protected array $metaData = [];
    public ?string $image;
    protected ?string $imageResolver;
    protected array $model = [];
    protected string $itemKey;
    protected string $group = '';

    public function __construct($id, $name, $price, $quantity = 1, $taxRate = 0, $model = null, $metaData = [], $image = null, $imageResolver = null, $group = '')
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

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    public function getImage()
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

    public function getItemKey(): string
    {
        return $this->itemKey;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setMetaData(array $metaData): void
    {
        $this->metaData = $metaData;
    }

    public function getMetaData(): array
    {
        return $this->metaData;
    }

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

    protected function generateItemKey(): string
    {
        return md5($this->id . serialize($this->metaData));
    }

    protected function setModel(array | Model $model): void
    {
        if(is_array($model)){
            $this->model = $model;
            return;
        }

        $this->model['id'] = $model->id;
        $this->model['class'] = get_class($model);	
    }

    public function getModel(): Model | null
    {
        if(empty($this->model)){
            return null;
        }

        return (new $this->model['class'])->find($this->model['id']);
    }

    public function price( $ex = true )
    {
        if($ex){
            return $this->price;
        }

        return $this->price * (1 + $this->taxRate);
    }

    public function total( $ex = true )
    {
        return $this->price($ex) * $this->quantity;
    }
}
