# Commands

## Remove cache


All cache for a given paths will be lost:

``` bash
$ php app/console liip:imagine:cache:remove path1 path
```


If you use --filters parameter, all cache for a given filters will be lost:

``` bash
$ php app/console liip:imagine:cache:remove --filters=thumb1 --filters=thumb2
```

You can combine these parameters:

``` bash
$ php app/console liip:imagine:cache:remove path1 path2 --filters=thumb1 --filters=thumb2
```

Cache for all paths and filters will be lost when executing this command without parameters :

``` bash
$ php app/console liip:imagine:cache:remove
```

## Resolve cache


``` bash
$ php app/console liip:imagine:cache:resolve path1 path2 --filters=thumb1
```

Cache for this two paths will be resolved with passed filter.
As a result you will get:
``` bash
    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb1/path2
```

You can pass few filters:

``` bash
$ php app/console liip:imagine:cache:resolve path1 --filters=thumb1 --filters=thumb2
```

As a result you will get:
``` bash
    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb2/path1
```

If you omit --filters parameter then to resolve given paths will be used all configured and available filters in application:
``` bash
$ php app/console liip:imagine:cache:resolve path1
```

As a result you will get:
``` bash
    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb2/path1
```

[Back to the index](index.md)
