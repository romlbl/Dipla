<?php

namespace App\Services;

use ImageKit\ImageKit;

class ImageKitService
{
    protected ImageKit $imageKit;

    public function __construct()
    {
        $this->imageKit = new ImageKit(
            config('services.imagekit.public_key'),
            config('services.imagekit.private_key'),
            config('services.imagekit.url_endpoint'),
        );
    }

    /**
     * Upload une image et retourne son URL.
     */
    public function upload(string $filePath, string $folder = 'dipla/products'): string
    {
        $result = $this->imageKit->upload([
            'file' => base64_encode(file_get_contents($filePath)),
            'fileName' => uniqid('img_') . '.webp',
            'folder' => $folder,
            'useUniqueFileName' => true,
        ]);

        return $result->result->url;
    }

    /**
     * Supprime une image à partir de son URL (retrouve le fichier par son nom).
     */
    public function delete(string $url): void
    {
        $fileName = basename(parse_url($url, PHP_URL_PATH));

        $files = $this->imageKit->listFiles([
            'searchQuery' => 'name = "' . $fileName . '"',
        ]);

        if (!empty($files->result)) {
            $this->imageKit->deleteFile($files->result[0]->fileId);
        }
    }

    /**
     * Upload depuis une URL : ImageKit télécharge lui-même l'image.
     */
    public function uploadFromUrl(string $url, string $folder = 'dipla/products'): string
    {
        $ext = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        $ext = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $ext : 'jpg';

        $result = $this->imageKit->upload([
            'file' => $url,
            'fileName' => uniqid('img_') . '.' . $ext,
            'folder' => $folder,
            'useUniqueFileName' => true,
        ]);

        if (($result->result?->fileType ?? null) !== 'image') {
            if (! empty($result->result?->fileId)) {
                $this->imageKit->deleteFile($result->result->fileId); // pas d'image : on nettoie
            }

            throw new \RuntimeException('Le fichier n\'est pas une image.');
        }

        return $result->result->url;
    }
}