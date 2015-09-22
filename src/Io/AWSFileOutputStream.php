<?php

namespace Flagship\Components\Helpers\Io;

use Aws\S3\S3Client;
use Flagship\Components\Helpers\Io\Abstracts\OutputStreamAbstract;
use Flagship\Components\Helpers\Io\Interfaces\Closable;
use Flagship\Components\Helpers\Io\Interfaces\Flushable;
use Flagship\Components\Helpers\Io\Exceptions\IOException;

class AWSFileOutputStream extends OutputStreamAbstract implements Closable, Flushable
{
    protected $bucket;
    protected $region;
    protected $key = null; // filename on AWS S3 Storage
    protected $client;
    protected $output;

    public function __construct($credentials, $region, $bucket)
    {
        $this->bucket = $bucket;
        $this->region = $region;

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
        $this->key = date('Ym').'/'.basename($filename);

        try {
            $this->output = $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $this->key,
                'Body' => \GuzzleHttp\Psr7\stream_for(fopen($filename, 'r+')),
                'ACL' => 'private',
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

        return $result['ObjectUrl'];
    }
}
