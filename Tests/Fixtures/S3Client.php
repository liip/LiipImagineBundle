<?php

namespace Aws\S3;

class S3Client
{
    public function doesBucketExist($bucket, $accept403, $options) { }

    public function doesObjectExist($bucket, $key, $options) { }

    public function putObject($args) { return new Guzzle\Service\Resource\Model(); }

    public function deleteObject($args) { }

    public function getObjectUrl($bucket, $key, $expires, $args) { }
}
