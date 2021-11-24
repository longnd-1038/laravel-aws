<?php

namespace App\Http\Controllers;

use Aws\CommandPool;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AWSController extends Controller
{
    protected $s3Instance;

    public function __construct()
    {
        $this->s3Instance = new S3Client([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION', 'us-west-2')
        ]);
    }

    public function testBatchGetMultifile()
    {
        $keys = ['test.txt', 'girl'];
        $commands = [];
        foreach ($keys as $key) {
            $commands[] = $this->s3Instance->getCommand('GetObject', [
                    'Bucket' => env('AWS_BUCKET'),
                    'Key'    => $key,
            ]);
        }

        $responses = CommandPool::batch($this->s3Instance, $commands);

        foreach ($responses as $response) {
            $data = $response['Body'];
            $fileContents[] = $data->getContents();
        }

        return $fileContents;
    }

    public function testBatchUploadMultiFile($uploadFiles)
    {
        $commands = [];
        foreach ($uploadFiles as $uploadFile) {
            $commands[] = $this->s3Instance->getCommand('PutObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $uploadFile['key'],
                'Body' => $uploadFile['body'],
            ]);
        }

        CommandPool::batch($this->s3Instance, $commands);
    }
}
