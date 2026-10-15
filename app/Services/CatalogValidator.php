<?php

namespace App\Services;

/**
 * Lignes brutes -> produits prêts pour la DB + liste des rejets (avec raison).
 * Pur PHP : aucun appel réseau, aucun coût.
 */
class CatalogValidator
{
    private const MAX_IMAGES = 4;

    /** @return array{valid: array, rejected: array} */
    public function check(array $rows): array
    {
        $valid = [];
        $rejected = [];

        foreach ($rows as $i => $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $label = $title !== '' ? $title : 'Ligne '.($i + 1);

            $description = trim((string) ($row['description'] ?? ''));
            $price = $this->price($row['price'] ?? '');
            $images = $this->images($row['images'] ?? '');

            $reason = match (true) {
                $title === '' => 'titre manquant',
                $description === '' => 'description manquante',
                $price === false => 'prix invalide',
                $images === [] => 'aucune image',
                default => null,
            };

            if ($reason) {
                $rejected[] = ['title' => mb_substr($label, 0, 80), 'reason' => $reason];
                continue;
            }

            $valid[] = [
                'title' => mb_substr($title, 0, 255),
                'price' => $price, // null = prix variable
                'description' => mb_substr($description, 0, 5000),
                'type' => ($row['type'] ?? '') === 'service' ? 'service' : 'produit',
                'keywords' => mb_substr(trim((string) ($row['keywords'] ?? '')), 0, 255),
                'images' => $images,
            ];
        }

        return ['valid' => $valid, 'rejected' => $rejected];
    }

    /** @return float|null|false  null = variable, false = invalide */
    private function price(mixed $value): float|null|false
    {
        $value = str_replace([' ', '€', ','], ['', '', '.'], trim((string) $value));

        if ($value === '') {
            return null;
        }

        if (! is_numeric($value) || $value < 0 || $value > 999999.99) {
            return false;
        }

        return round((float) $value, 2);
    }

    /** "url1 | url2" -> URLs http(s) valides, max 4. */
    private function images(mixed $value): array
    {
        $parts = preg_split('/[\s|;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);

        $urls = array_filter($parts, function ($url) {
            $url = rtrim($url, ',');

            return filter_var($url, FILTER_VALIDATE_URL)
                && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
        });

        return array_slice(array_values(array_unique(array_map(fn ($u) => rtrim($u, ','), $urls))), 0, self::MAX_IMAGES);
    }
}