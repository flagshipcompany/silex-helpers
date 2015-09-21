<?php

namespace Flagship\Components\Helpers\Io;

use Aws\S3\S3Client;
use Flagship\Components\Helpers\Io\Abstracts\OutputStreamAbstract;
use Flagship\Components\Helpers\Io\Interfaces\Closable;
use Flagship\Components\Helpers\Io\Interfaces\Flushable;
use Flagship\Components\Helpers\Io\Exceptions\IOException;

class AWSFileOutputStream extends OutputStreamAbstract implements Closable, Flushable
{
    protected $config;
    protected $output;

    public function __construct($credentials, $region, $bucket)
    {
        $this->stream = new S3Client([
            'credentials' => $credentials,
            'region' => $region,
            'version' => 'latest',
        ]);

        $this->resetConfig($bucket);

        return $this;
    }

    public function close()
    {
        if (!$this->stream) {
            $this->flush();
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
        if (!$this->stream) {
            throw new IOException('Resource access has been closed', 500);
        }

        $this->config['Key'] = date('Ym').'/'.basename($filename);
        $this->config['Body'] = \Guzzle\Http\EntityBody::factory(fopen($filename, 'r'));
        $this->config['Params']['ContentType'] = mime_content_type($filename); // for external
        $this->config['Params']['mimeype'] = $this->config['Params']['ContentType']; // for local

        try {
            $this->output = $this->stream->upload(
                $this->config['Bucket'],
                $this->config['Key'],
                $this->config['Body'],
                $this->config['ACL'],
                [
                    'params' => $this->config['Params'],
                ]
            );
            $this->resetConfig($this->config['Bucket']);

            return $this;
        } catch (\Exception $e) {
            throw new IOException($e->getMessage(), $e->getCode());
        }
    }

    public function getOutput()
    {
        return $this->output;
    }

    protected function resetConfig($bucket)
    {
        $this->config = [
            'Bucket' => $bucket,
            'ACL' => 'private', // or public-read
            'Params' => [
                'ServerSideEncryption' => 'AES256',
            ],
        ];
    }
}
