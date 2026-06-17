<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private Cloudinary $cloudinary;
    private string $folder;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary(
            sprintf(
                'cloudinary://%s:%s@%s',
                config('filesystems.cloudinary.api_key'),
                config('filesystems.cloudinary.api_secret'),
                config('filesystems.cloudinary.cloud_name')
            )
        );

        $this->folder = config('filesystems.cloudinary.folder', 'blogflow');
    }

    /**
     * Upload une image ou vidéo sur Cloudinary.
     * Retourne l'URL sécurisée (https://res.cloudinary.com/...).
     */
    public function upload(UploadedFile $file, string $subfolder = 'articles'): string
    {
        $mimeType     = $file->getMimeType() ?? '';
        $resourceType = str_starts_with($mimeType, 'video/') ? 'video' : 'image';

        $result = $this->cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder'          => "{$this->folder}/{$subfolder}",
                'resource_type'   => $resourceType,
                'unique_filename' => true,
                'overwrite'       => false,
            ]
        );

        return $result['secure_url'];
    }

    /**
     * Supprime un fichier sur Cloudinary depuis son URL sécurisée.
     */
    public function delete(string $secureUrl): void
    {
        $publicId     = $this->extractPublicId($secureUrl);
        $resourceType = str_contains($secureUrl, '/video/') ? 'video' : 'image';

        if ($publicId) {
            $this->cloudinary->uploadApi()->destroy(
                $publicId,
                ['resource_type' => $resourceType]
            );
        }
    }

    /**
     * Extrait le public_id depuis une URL Cloudinary.
     * Exemple :
     *   https://res.cloudinary.com/ddffet7qj/image/upload/v123/blogflow/articles/abc.jpg
     *   → blogflow/articles/abc
     */
    private function extractPublicId(string $url): ?string
    {
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(\.[a-z0-9]+)?$/i', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
