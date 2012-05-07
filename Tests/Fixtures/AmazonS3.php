<?php

class AmazonS3
{
	const ACL_PRIVATE = 'private';

	const ACL_PUBLIC = 'public-read';

    public function if_bucket_exists($bucket) { }

    public function if_object_exists($bucket, $object) { }

    public function create_object($bucket, $path, $options) { }

    public function delete_object($bucket, $path) { }

    public function get_object_url($bucket, $path, $preauth, $options) { }
}

class AmazonS3Response
{
    public function isOK() { }
}
