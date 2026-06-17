<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private Cloudinary $cloudinary;
    private string $folder;

    public function __construct()
    {
        Configuration::instance([
            'cloud' => [
                'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
                'api_key'    => config('filesystems.disks.cloudinary.api_key'),
                'api_secret' => config('filesystems.disks.cloudinary.api_secret'),
            ],
            'url' => ['secure' => true],
        ]);

        $this->cloudinary = new Cloudinary();
        $this->folder     = config('filesystems.disks.cloudinary.folder', 'blogflow');
    }

    /**
     * Upload un fichier image ou vidéo sur Cloudinary.
     * Retourne l'URL sécurisée du fichier uploadé.
     */
    public function upload(UploadedFile $file, string $subfolder = 'articles'): string
    {
        $resourceType = str_starts_with($file->getMimeType() ?? '', 'video/') ? 'video' : 'image';

        $result = (new UploadApi())->upload(
            $file->getRealPath(),
            [
                'folder'        => "{$this->folder}/{$subfolder}",
                'resource_type' => $resourceType,
                'use_filename'  => false,
                'unique_filename' => true,
                'overwrite'     => false,
            ]
        );

        return $result['secure_url'];
    }

    /**
     * Supprime un fichier sur Cloudinary à partir de son URL sécurisée.
     * Détermine automatiquement le public_id et le resource_type.
     */
    public function delete(string $secureUrl): void
    {
        $publicId    = $this->extractPublicId($secureUrl);
        $resourceType = str_contains($secureUrl, '/video/') ? 'video' : 'image';

        if ($publicId) {
            (new UploadApi())->destroy($publicId, ['resource_type' => $resourceType]);
        }
    }

    /**
     * Extrait le public_id depuis une URL Cloudinary.
     * Ex: https://res.cloudinary.com/ddffet7qj/image/upload/v123/blogflow/articles/abc.jpg
     *     → blogflow/articles/abc
     */
    private function extractPublicId(string $url): ?string
    {
        // Retire l'extension et tout ce qui précède le dossier de upload
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(\.[a-z0-9]+)?$/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
