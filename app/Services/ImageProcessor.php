<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * URLs d'images -> ImageKit (en parallèle) -> URLs au format 2/3.
 * Image entière, jamais rognée, fond flou de la même image.
 */
class ImageProcessor
{
    private const TRANSFORM = 'w-800,h-1200,cm-pad_resize,bg-blurred,f-auto,q-85';

    /**
     * @param  string[]  $urls
     * @return array<string, string|null>  url source => url finale (null si échec)
     */
    public function processMany(array $urls): array
    {
        $urls = array_values(array_unique(array_filter(
            $urls,
            fn ($u) => in_array(parse_url($u, PHP_URL_SCHEME), ['http', 'https'], true)
        )));

        if ($urls === []) {
            return [];
        }

        $key = config('services.imagekit.private_key');

        $responses = Http::pool(function (Pool $pool) use ($urls, $key) {
            foreach ($urls as $i => $url) {
                $pool->as((string) $i)
                    ->withBasicAuth($key, '')
                    ->asMultipart()
                    ->timeout(25)
                    ->post('https://upload.imagekit.io/api/v1/files/upload', [
                        ['name' => 'file', 'contents' => $url],
                        ['name' => 'fileName', 'contents' => uniqid('img_').'.jpg'],
                        ['name' => 'folder', 'contents' => 'dipla/products'],
                        ['name' => 'useUniqueFileName', 'contents' => 'true'],
                    ]);
            }
        });

        $out = [];

        foreach ($urls as $i => $url) {
            $response = $responses[(string) $i] ?? null;
            $out[$url] = null;

            if (! $response instanceof Response || ! $response->successful()) {
                continue;
            }

            if ($response->json('fileType') !== 'image') {
                // Pas une image : on supprime le fichier créé.
                if ($id = $response->json('fileId')) {
                    Http::withBasicAuth($key, '')->timeout(10)->delete("https://api.imagekit.io/v1/files/{$id}");
                }
                continue;
            }

            $out[$url] = $response->json('url').'?tr='.self::TRANSFORM;
        }

        return $out;
    }
}