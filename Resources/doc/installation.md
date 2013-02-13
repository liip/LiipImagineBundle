# Installation

To install this bundle, you'll need both the [Imagine library](/avalanche123/Imagine)
and this bundle. Installation depends on how your project is setup:

## Step 1: Installation

Add the following lines to your ``deps`` file

```
[Imagine]
    git=http://github.com/avalanche123/Imagine.git
    target=imagine
    version=v0.2.0

[LiipImagineBundle]
    git=http://github.com/liip/LiipImagineBundle.git
    target=bundles/Liip/ImagineBundle
```

Next, update your vendors by running:

``` bash
$ ./bin/vendors install
```

## Step 2: Configure the autoloader

Add the following entries to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...

    'Imagine'   => __DIR__.'/../vendor/imagine/lib',
    'Liip'      => __DIR__.'/../vendor/bundles',
));
```

## Step 3: Enable the bundle

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

## Step 4: Register the bundle's routes

Finally, add the following to your routing file:

``` yaml
# app/config/routing.yml

_imagine:
    resource: .
    type:     imagine
```

Congratulations! You're ready to rock your images!

[Back to the index](index.md)
