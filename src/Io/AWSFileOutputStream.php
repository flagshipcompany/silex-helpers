<?php

namespace Flagship\Components\Helpers\Io;

use Aws\S3\S3Client;
use Flagship\Components\Helpers\Io\Abstracts\OutputStreamAbstract;
use Flagship\Components\Helpers\Io\Interfaces\Closeable;
use Flagship\Components\Helpers\Io\Interfaces\Flushable;
use Flagship\Components\Helpers\Io\Exceptions\IOException;

class AWSFileOutputStream extends OutputStreamAbstract implements Closeable, Flushable
{
    protected $bucket;
    protected $region;
    protected $acl;
    protected $key = null; // filename on AWS S3 Storage
    protected $client;
    protected $output;

    // for more possible acl value , check : http://docs.aws.amazon.com/AmazonS3/latest/dev/acl-overview.html
    public function __construct($credentials, $region, $bucket, $acl = 'private')
    {
        $this->bucket = $bucket;
        $this->region = $region;
        $this->acl = $acl;

        $this->client = new S3Client([
            'credentials' => $credentials,
            'region' => $region,
            'version' => '2006-03-01',
        ]);

        // $this->client->registerStreamWrapper();

        return $this;
    }

    public function close()
    {
        if (!$this->stream) {
            $this->flush();
            fclose($this->stream);
            $this->stream = null;
        }
    }

    public function flush()
    {
        if (!$this->stream) {
            throw new IOException('Resource access has been closed', 500);
        }
    }

    public function write($filename, $offset = false, $length = false)
    {
        $filenameArr = explode('/', $filename);

        $this->key = array_pop($filenameArr);
        $this->key = date('Ym').'/'.array_pop($filenameArr).'/'.$this->key;

        try {
            $this->output = $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $this->key,
                'Body' => \GuzzleHttp\Psr7\stream_for(fopen($filename, 'r+')),
                'ACL' => $this->acl,
                'ServerSideEncryption' => 'AES256',
                'ContentType' => mime_content_type($filename),
            ]);

            return $this;
        } catch (\Exception $e) {
            throw new IOException($e->getMessage(), $e->getCode());
        }
    }

    public function getRemoteUrl($filename)
    {
        $result = $this->write($filename)->output;

        return $result['ObjectURL'];
    }
}
