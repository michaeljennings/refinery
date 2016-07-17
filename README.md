# Refinery [![Latest Stable Version](https://poser.pugx.org/michaeljennings/refinery/v/stable)](https://packagist.org/packages/michaeljennings/refinery) [![Build Status](https://travis-ci.org/michaeljennings/refinery.svg?branch=v0.1.1)](https://travis-ci.org/michaeljennings/refinery)

This project aims to add a layer of abstraction between your backend data store and the front end code. If you make an edit to 
the way your data is stored this can often end up in you needing to make a lot of front end changes. This project helps by 
formatting your data in one place so that if your back end changes, it won't effect the rest of your website.

## Example

Below is a basic example of how to use refinery.

```php
// Here we have a product we want to refine
class Product
{
    public $price = 10
    public $description = 'Something really cool!'
}

// So we create a product refinery where we set the product template
class ProductRefinery extends Refinery
{
    public function setTemplate($product)
    {
        return [
            'price' => number_format($product->price, 2),
            'description' => $product->description,
            'full_description' => $product->description . ' For only £' . number_format($product->price, 2),
        ];
    }
}

// Then we create the instances and refine the product
$product = new Product();
$refinery = new ProductRefinery();

var_dump($refinery->refine($product));
 
// The above will output:
// ['price' => '10.00', 'description' => 'Something really cool!', 'full_description' => 'Something really cool! For only £10.00']
```

## Navigation

- [Installation](#installation)
- [Usage](#usage)
    - [Example Usage](#example-usage)
- [Attaching Data](#attaching-data)

## Installation

Include the package in your `composer.json`.

    "michaeljennings/refinery": "~1.0";

Run `composer install` or `composer update` to download the dependencies.

## Usage

To create a new refinery simply extend the `Michaeljennings\Refinery\Refinery`.

```php
use Michaeljennings\Refinery\Refinery;

class Foo extends Refinery 
{
  
}
```

The refinery has one abstract method which is `setTemplate`. The setTemplate method is passed the item being refined and then you just need to return it in the format you want.

```php
class Foo extends Refinery 
{
  public function setTemplate($data)
  {
    // Refine data
  }
}
```

To pass data to the refinery, create a new instance of the class and then use the `refine` method.

```php
$foo = new Foo;

$foo->refine($data);
```

You can pass the refinery either a single item or a multidimensional item.

```php
$foo = new Foo;

$data = $foo->refine(['foo', 'bar']);
$multiDimensionalData = $foo->refine([['foo'], ['bar']]);
```

#### Example Usage
As an example if I had a product and I wanted to make sure that its price always had two decimal figures I could do the
following.

```php
class Product {
  public price = '10';
}

class ProductRefinery extends Refinery {
  public function setTemplate($product)
  {
    return [
      'price' => number_format($product->price, 2),
    ];
  }
}

$product = new Product();
$refinery = new ProductRefinery();

$refinedProduct = $refinery->refine($product); // ['price' => 10.00]

```

### Attaching Data

Occasionally you may also need to refine data with in an item, to do this you can use the we can use attachments.

To attach data we first need to set up a method on the refinery with the name of the item we are trying to attach. For 
example if I had a product and I wanted to refine its options to it I would create an options method. This method needs 
to return the attach method which takes one parameter which is the name of the class we shall use to refine the 
attachment.

```php
class ProductRefinery extends Refinery {
  public function setTemplate($product)
  {
    // Refine Product
  }
  
  protected function options()
  {
    return $this->attach('OptionRefinery');
  }
}

class OptionRefinery extends Refiner {
  public function setTemplate($option)
  {
    // Refine Product Option
  }
}
```

Then to refine that data all you need to do is run the `bring` method and specify the attachments you want to bring.

```php
class Product {
  public $options = [
    // options
  ];
}

$product = new Product();
$refinery = new ProductRefinery();

$refinery->bring('options')->refine($product);
```

To bring multiple attachments you can either add them as multiple arguments or pass an array.

```php
$refinery->bring('options', 'foo', 'bar');
$refinery->bring(['options', 'foo', 'bar']);
```

You can also attach items into attachments by using a key value array.

```php
$refinery->bring(['options' => 'foo']);
$refinery->bring(['options' => ['foo', 'bar']]);
```

You can also run methods from the attached item by passing the name of the attachment as the key and a closure as the 
argument.

```php
$refinery->bring(['options' => function($option) {
  $option->where('online', '=', true);
  return $option->get();
}]);
```
