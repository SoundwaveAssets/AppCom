<?php

namespace App\Lib;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FirebaseStorageService
{
    public function uploadProductImage(UploadedFile $file): string
    {
        $credentialsPath = (string) config('services.firebase.credentials');
        $bucketName = (string) config('services.firebase.storage_bucket');

        $storage = $credentialsPath !== ''
            ? new StorageClient(['keyFilePath' => $credentialsPath])
            : new StorageClient();

        $bucket = $storage->bucket($bucketName);
        $filename = 'products/'.date('Y/m').'/'.Str::uuid().'-'.$file->getClientOriginalName();

        $bucket->upload(
            fopen($file->getRealPath(), 'r'),
            ['name' => $filename, 'predefinedAcl' => 'publicRead']
        );

        return "https://storage.googleapis.com/{$bucketName}/{$filename}";
    }
}
