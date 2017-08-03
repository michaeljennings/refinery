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
- [Multidimensional Refining](#multidimensional-refining)
- [Advanced Refining](#advanced-refining)
    - [Using External Data](#using-external-data)
    - [Custom Attachments](#custom-attachments)
    - [Raw Attachments](#raw-attachments)

## Installation

Run `composer require michaeljennings/refinery`.

Or add the package to your `composer.json`.

```
"michaeljennings/refinery": "~1.0";
```

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

### Example Usage
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

## Multidimensional Refining

When you are working with database data, particularly relational data, it's common that you won't just be refining the main entity, you may have parent and child data that also needs to be refined.

This is best displayed with an example. Below we have a product that has many variants, and belongs to a category.

```php
$product = [
    'name' => 'Awesome Product',
    'description' => 'This is an awesome product',
    'price' => 10.00,
    'category' => [
        'name' => 'Awesome Products',
    ],
    'variants' => [
        [
            'size' => 'Medium',
        ],
        [
            'size' => 'Large',
        ]
    ]
]
```

This parent and child data will usually be refined in the same way in every area it is used within your application, therefore it makes sense to move it into it's own class so that it can be reused.

To do that we use attachments.

Below we have a product refinery, that has category and variant attachments.

```php
class ProductRefinery extends Refinery
{
    public function setTemplate($product)
    {
        return [
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => '£' . number_format($product['price'], 2),
        ];
    }

    public function category()
    {
        return $this->attach(CategoryRefinery::class);
    }

    public function variants()
    {
        return $this->attach(CategoryRefinery::class);
    }
}
```

These attachments will only be brought with the item when the `bring` method is called when refining.

```php
// Will just refine the product data.
$refinedProduct = $productRefinery->refine($product);

// Will refine the product, category, and the variants.
$refinedProductWithAttachments $productRefinery->bring('category', 'variants')->refine($product);
```

When you attempt to bring attachments it will look for methods on the refinery class with the specified attachment name.

For example if we were to do the following:

```php
$refinedProductWithAttachments $productRefinery->bring('category')->refine($product);
```

There would need to be a method called `category` on our product refinery.

The refinery will then attempt to pass the category property from the product to the category refinery.

Attachments can also be multidimensional. For example below we are bringing a product with it's category, and then we are bringing the category's meta data.

```php
$refined = $productRefinery->bring(['category' => ['meta']])->refine($product);
```

This will look for the category attachment on the product refinery, and then a meta attachment on the category refinery.

## Advanced Refining

### Using External Data

When refining you may need to access data that is not part of the main object.

For example, below we want to get the correct currency symbol for the country the user is in, however the country cannot be accessed from the product.

To get around this we use the `with` method to pass through the current country to the product refinery.

```php
$country = [
    'name' => 'United Kingdom',
    'symbol' => '£',
];

$refined = $productRefinery->with(['country' => $country])->refine($product);
```

Then in the `setTemplate` method in the refinery we can access the country as shown below.

```php
class ProductRefinery extends Refinery
{
    public function setTemplate($product)
    {
        $symbol = $this->country['symbol'];
        // OR
        $symbol = $this->attributes['country']['symbol'];

        return [
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => $symbol . number_format($product['price'], 2),
        ];
    }
}
```

### Custom Attachments

Occasionally you may want to set attachments that don't actually exist in the original data.

For example below we have added a `largeVariant` attachment to our product refinery.

By passing a closure to the second argument of the `attach` method we can choose what data we want to send to the refinery. The closure will be passed the current item being refined, which in this case is the product.

```php
class ProductRefinery extends Refinery
{
    public function largeVariant()
    {
        return $this->attach(VariantRefinery::class, function($product) {
            return array_filter($product['variants'], function($variant) {
                return $variant['size'] == 'Large';
            });
        });
    }
}
```

### Raw Attachments

Refining can also be performed without a preset class, for instance if you want to refine a multidimensional array to a single array.

Below we have added an attachment which gets all of the sizes for a product and returns it as an array.

```php
class ProductRefinery extends Refinery
{
    public function sizes()
    {
        return $this->attach(function($product) {
            $sizes = [];

            foreach ($product['variants'] as $variant) {
                $sizes[] = $variant['size'];
            }

            return $sizes;
        });
    }
}
```