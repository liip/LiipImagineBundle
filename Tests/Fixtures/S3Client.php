<?php

namespace Aws\S3;

class S3Client
{
    public function doesBucketExist($bucket, $accept403, $options = array()) { }

    public function doesObjectExist($bucket, $key, $options = array()) { }

    public function putObject($args) { }

    public function deleteObject($args) { }

    public function getObjectUrl($bucket, $key, $expires = 0, $args = array()) { }
}
