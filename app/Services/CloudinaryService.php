<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private ?Cloudinary $cloudinary = null;
    private string $folder;

    public function __construct()
    {
        $this->folder = config('filesystems.cloudinary.folder', 'blogflow');
    }

    /**
     * Initialisation lazy — uniquement lors du premier upload ou delete.
     * Évite un crash au boot si les variables Cloudinary sont absentes.
     */
    private function client(): Cloudinary
    {
        if ($this->cloudinary === null) {
            $cloudName = config('filesystems.cloudinary.cloud_name');
            $apiKey    = config('filesystems.cloudinary.api_key');
            $apiSecret = config('filesystems.cloudinary.api_secret');

            if (!$cloudName || !$apiKey || !$apiSecret) {
                throw new \RuntimeException(
                    'Configuration Cloudinary manquante. Vérifiez CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY et CLOUDINARY_API_SECRET.'
                );
            }

            $this->cloudinary = new Cloudinary(
                sprintf('cloudinary://%s:%s@%s', $apiKey, $apiSecret, $cloudName)
            );
        }

        return $this->cloudinary;
    }

    /**
     * Upload une image ou vidéo sur Cloudinary.
     * Retourne l'URL sécurisée (https://res.cloudinary.com/...).
     */
    public function upload(UploadedFile $file, string $subfolder = 'articles'): string
    {
        $mimeType     = $file->getMimeType() ?? '';
        $resourceType = str_starts_with($mimeType, 'video/') ? 'video' : 'image';

        $result = $this->client()->uploadApi()->upload(
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
        // Ne tente pas de supprimer les URLs externes (Unsplash, etc.)
        if (!str_contains($secureUrl, 'res.cloudinary.com')) {
            return;
        }

        $publicId     = $this->extractPublicId($secureUrl);
        $resourceType = str_contains($secureUrl, '/video/') ? 'video' : 'image';

        if ($publicId) {
            $this->client()->uploadApi()->destroy(
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
