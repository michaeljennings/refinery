# Refinery
This project aims to add a layer of abstraction between your backend data store and the front end code. If you make an edit to 
the way your data is stored this can often end up in you needing to make a lot of front end changes. This project helps by 
formatting your data in one place so that if your back end changes, it won't effect the rest of your website.

## Installation

Include the package in your `composer.json`.

    "michaeljennings/refinery": "dev-master";

Run `composer install` or `composer update` to download the dependencies.

## Usage

To create a new refinery simply extend the `Michaeljennings\Refinery\Refinery`.

```php
use Michaeljennings\Refinery\Refinery;

class Foo extends Refinery {
  
}
```

The refinery has one abstract method which is `setTemplate`. This method must return a Closure which will be used to format your
data.

```php
class Foo extends Refinery {
  public function setTemplate()
  {
    return function($data)
    {
      // Refine data
    };
  }
}
```

To pass data to the refinery, create a new instance of the class and then use the `refine` method.

```php
$foo = new Foo;

$foo->refine($data);
```

For example if I had a product object and I wanted to make sure that it's price always has two decimal places I could do the 
following.

```php
class Product extends Refinery {
  public function setTemplate()
  {
    return function($product)
    {
      return array(
        'price' => number_format($product->price, 2),
      );
    }
  }
}
    
$foo = new Foo;

$refinedProduct = $foo->refine($product);
```
