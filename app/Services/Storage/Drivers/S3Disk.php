<?php

namespace App\Services\Storage\Drivers;

use App\Config\Storage;
use App\Services\Storage\Exceptions\StorageException;
use App\Services\Storage\FileSystem;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use CodeIgniter\HTTP\Files\UploadedFile;

class S3Disk implements FileSystem
{
    protected $disk;
    protected $s3;
    protected $bucket;

    /**
     * S3Disk constructor.
     *
     * @param Storage $config
     */
    public function __construct(Storage $config)
    {
        $this->disk = $config->disk;

        $this->s3 = new S3Client([
            'version' => 'latest',
            'region' => $config->s3['defaultRegion'],
            'credentials' => [
                'key' => $config->s3['accessKey'],
                'secret' => $config->s3['secretKey'],
            ],
            'endpoint' => $config->s3['endpoint'],
            'http' => [
                'verify' => $config->s3['verify'] ?? false
            ]
        ]);

        $this->bucket = $config->s3['bucket'];
    }

    /**
     * Get driver client.
     *
     * @return S3Client
     */
    public function getClient()
    {
        return $this->s3;
    }

    /**
     * Store or put file into s3 or compatible storage.
     *
     * @param $content
     * @param null $key
     * @param array $options
     * @return mixed
     */
    public function store($content, $key = null, $options = [])
    {
        $bucket = $options['bucket'] ?? $this->bucket;
        $acl = $options['acl'] ?? 'public-read';
        $fileName = $options['file_name'] ?? null;
        $path = empty($key) ? '' : rtrim($key, '/') . '/';

        $fileType = $options['mime_type'] ?? null;
        $fileSize = $options['file_size'] ?? 0;
        if ($content instanceof UploadedFile) {
            $fileType = $content->getMimeType();
            $fileSize = $content->getSize();

            if (empty($fileName)) {
                $fileName = $content->getRandomName();
            }
        }

        try {
            if (!$this->s3->doesBucketExist($bucket)) {
                $result = $this->s3->createBucket([
                    'Bucket' => $bucket,
                ]);
                if (!$result) {
                    throw StorageException::unableToWrite($bucket);
                }
            }

            $args = [
                'Bucket' => $bucket,
                'Key' => $path . $fileName,
                'ACL' => $acl,
                'ContentType' => $fileType,
            ];
            if ($content instanceof UploadedFile || is_resource($content)) {
                $args['Body'] = fopen($content->getTempName(), 'rb');
            } else if (is_string($content)) {
                $args['SourceFile'] = $content;
            } else {
                throw StorageException::contentIsInvalid();
            }
            $result = $this->s3->putObject($args);

            return [
                'disk' => $this->disk,
                'type' => $fileType,
                'size' => $fileSize,
                'path' => $path,
                'file_name' => $fileName,
                'file_path' => $path . $fileName,
                'file_url' => $result->get('ObjectURL'),
                'full_path' => $this->bucket . $path . $fileName,
            ];
        } catch (S3Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    /**
     * Get file from storage.
     *
     * @param null $key
     * @param array $options
     * @return mixed
     */
    public function get($key = null, $options = [])
    {
        $bucket = $options['bucket'] ?? $this->bucket;

        return $this->s3->getObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);
    }

    /**
     * Get public url of file in s3 storage.
     *
     * @param $key
     * @return mixed
     */
    public function url($key)
    {
        return $this->s3->getObjectUrl($this->bucket, $key);
    }

    /**
     * Get preSignedUrl url from s3 storage.
     *
     * @param $key
     * @param int $minutes
     * @return string
     */
    public function preSignedUrl($key, $minutes = 20)
    {
        $cmd = $this->s3->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $key
        ]);

        $request = $this->s3->createPresignedRequest($cmd, "+{$minutes} minutes");

        return (string)$request->getUri();
    }

    /**
     * Create a directory in s3 storage.
     *
     * @param $path
     * @param array $options
     * @return bool
     */
    public function makeDirectory($path, $options = [])
    {
        return $this->store(null, rtrim($path, '/') . '/');
    }

    /**
     * Copy data inside bucket of pass custom bucket destination.
     *
     * @param $keySource
     * @param $keyDestination
     * @param array $options
     * @return mixed
     */
    public function copy($keySource, $keyDestination, $options = [])
    {
        $destinationBucket = $options['destinationBucket'] ?? $this->bucket;
        $sourceBucket = $options['sourceBucket'] ?? $this->bucket;
        $acl = $options['acl'] ?? 'public-read';

        return $this->s3->copyObject([
            'Bucket' => $destinationBucket,
            'Key' => $keySource,
            'CopySource' => "{$sourceBucket}/{$keyDestination}",
            'ACL' => $acl
        ]);
    }

    /**
     * Move data inside or between bucket.
     *
     * @param $keySource
     * @param $keyDestination
     * @param array $options
     * @return mixed
     */
    public function move($keySource, $keyDestination, $options = [])
    {
        if (preg_match('/\/$/', $keySource)) {
            return $this->moveObjects($keySource, $keyDestination, $options);
        }

        $sourceBucket = $options['sourceBucket'] ?? $this->bucket;

        $result = $this->copy($keySource, $keyDestination, $options);

        if ($result) {
            $this->s3->deleteObject([
                'Bucket' => $sourceBucket,
                'Key' => $keySource,
            ]);
        }

        return $result;
    }

    /**
     * Move directories objects.
     *
     * @param $keySource
     * @param $keyDestination
     * @param array $options
     * @return Result
     */
    public function moveObjects($keySource, $keyDestination, $options = [])
    {
        $sourceBucket = $options['sourceBucket'] ?? $this->bucket;

        $keySource = rtrim($keySource, '/') . '/';
        $keyDestination = rtrim($keyDestination, '/') . '/';

        $results = $this->s3->listObjects([
            'Bucket' => $sourceBucket,
            'Prefix' => $keySource,
        ]);

        $movedKeys = [];
        foreach ($results['Contents'] ?: [] as $file) {
            $targetKeyDest = str_replace($keySource, $keyDestination, $file['Key']);
            $copyResult = $this->copy($file['Key'], $targetKeyDest, $options);
            if ($copyResult) {
                $movedKeys[] = ['Key' => $file['Key']];
            }
        }

        return $this->deleteObjects($movedKeys);
    }

    /**
     * Delete data inside s3 storage.
     *
     * @param $key
     * @return Result
     */
    public function delete($key)
    {
        if (is_array($key)) {
            return $this->deleteObjects($key);
        }

        return $this->s3->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);
    }

    /**
     * Delete multiple objects from storage.
     *
     * @param $keys
     * @return Result
     */
    public function deleteObjects($keys)
    {
        return $this->s3->deleteObjects([
            'Bucket' => $this->bucket,
            'Delete' => [
                'Objects' => $keys
            ],
        ]);
    }

    /**
     * Delete multiple objects by matching pattern of key from storage.
     *
     * @param $matchKey
     * @return void
     */
    public function deleteMatchingObjects($matchKey)
    {
        $this->s3->deleteMatchingObjects($matchKey);
    }

}