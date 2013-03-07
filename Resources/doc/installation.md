# Installation

To install this bundle, you'll need both the [Imagine library](https://github.com/avalanche123/Imagine)
and this bundle.

### Step 1: Download LiipImagineBundle using composer

Tell composer to require LiipImagineBundle by running the command:

``` bash
$ php composer.phar require "liip/imagine-bundle:dev-master"
```

Composer will install the bundle to your project's `vendor/liip/imagine-bundle` directory.


## Step 2: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...

        new Liip\ImagineBundle\LiipImagineBundle(),
    );
}
```

## Step 3: Register the bundle's routes

Finally, add the following to your routing file:

``` yaml
# app/config/routing.yml

_imagine:
    resource: .
    type:     imagine
```

Congratulations! You're ready to rock your images!

[Back to the index](index.md)
