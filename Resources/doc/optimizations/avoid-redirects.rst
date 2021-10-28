

Avoid unnecessary redirects
===========================

If you can not use :doc:`symfony messenger <resolve-cache-images-in-background>`,
you can configure your webserver to avoid some redirects from the image
controller. The solution described in this documentation only works when using
the ``WebPathResolver``, not when you store the images in a place outside of
the web server.

When an image has not been cached, the ``imagine_filter`` generates the image
link as a path to the image controller. The image controller creates the image
and then redirects the client to the generated image.

By default, this redirection is done with status ``302`` (moved temporarily).
This is important, because when you later clear the cache, the controller needs
to be called again so that the image is regenerated. When returning ``301``, we
tell the client that the resource moved permanently. The client will cache this
information and directly request the (not existing) cached image even when the
twig filter generates the controller URL again.

If you want to safely use ``301`` to avoid unnecessary redirects, you need to
configure your webserver to route requests for missing images to Symfony.

.. code-block:: bash

    # bypass thumbs cache image files
    location ~ ^/media/cache/resolve {
      expires 1M;
      access_log off;
      add_header Cache-Control "public";
      try_files $uri $uri/ /index.php?$query_string;
    }

    location ~* .(js|jpg|jpeg|gif|png|css|tgz|gz|rar|bz2|doc|pdf|ppt|tar|wav|bmp|rtf|swf|ico|flv|txt|woff|woff2|svg)$ {
        expires 30d;
        add_header Pragma "public";
        add_header Cache-Control "public";
    }

With such a configuration, you can safely set ``liip_imagine.controller.redirect_response_code``
to 301.

If you configure your web server like this, you can also use the
``imagine_filter_cache`` to never redirect your client.
