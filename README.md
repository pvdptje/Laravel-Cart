### Laravel Cart

A simple shopping cart implementation.

## How to install
Download this repository as a zip file and extract the contents into your Laravel folder (excluding this readme file).

## Usage

**Adding Items**
You can add items to the cart by creating a new CartItem:
```
use App\Cart\CartItem;

$item = new CartItem(
    '1',                  // Product ID
    'Product Name',       // Name
    10.0,                 // Price
    2,                    // Quantity
    0.1,                  // Tax rate (10%)
    'regular',            // Group (optional, e.g., for shipping),
    'images/mythumbnail.png'
);

// Add the item to the cart
$cart->add($item);
```

See class `App\Cart\Cart` for more methods.
