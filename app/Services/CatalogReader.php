<?php

namespace App\Services;

use OpenSpout\Reader\ReaderInterface;
use RuntimeException;
use ZipArchive;

/**
 * Fichier -> ['headers' => [...], 'rows' => [[col => valeur], ...]].
 * Tous les formats sont envoyés à CatalogAiExtractor après décompactage éventuel du ZIP.
 */
class CatalogReader
{
    public const MAX_ROWS = 5000;
    private const SUPPORTED = ['csv', 'tsv', 'xlsx', 'ods', 'json', 'xml'];
    private const NATIVE = ['csv', 'tsv', 'xlsx', 'ods', 'json', 'xml'];

    public function __construct(private CatalogAiExtractor $ai, private CatalogMapper $mapper)
    {
    }

    public function read(string $path, string $ext): array
    {
        $ext = strtolower($ext);
        $tmp = null;

        try {
            if ($ext === 'zip') {
                [$path, $ext] = $tmp = $this->unzip($path);
            }

            $data = null;

            if (in_array($ext, self::NATIVE, true)) {
                try {
                    $data = $this->readNative($path, $ext);
                } catch (RuntimeException) {
                    $data = null; // illisible en PHP : on tente l'IA
                }
            }

            $data = ($data && $data['rows'] !== [] && count($data['headers']) >= 2)
                ? $this->mapper->map($data)
                : $this->ai->extract($path, $ext);
        } finally {
            if ($tmp) {
                @unlink($tmp[0]);
            }
        }

        if (count($data['rows']) > self::MAX_ROWS) {
            throw new RuntimeException('Fichier trop gros : '.self::MAX_ROWS.' lignes maximum. Découpez-le en plusieurs fichiers.');
        }

        return $data;
    }

    /** ZIP : prend le 1er fichier de catalogue lisible. Retourne [chemin temporaire, extension]. */
    private function unzip(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('ZIP illisible ou corrompu.');
        }

        $allowed = [...self::SUPPORTED, 'pdf', 'docx', 'xls', 'txt', 'html'];

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = $stat['name'];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                // Ignore dossiers, métadonnées Mac, chemins douteux, fichiers énormes.
                if (str_ends_with($name, '/') || str_contains($name, '__MACOSX') || str_contains($name, '..')
                    || ! in_array($ext, $allowed, true) || $stat['size'] > 10 * 1024 * 1024) {
                    continue;
                }

                $tmp = tempnam(sys_get_temp_dir(), 'cat');
                file_put_contents($tmp, $zip->getFromIndex($i));

                return [$tmp, $ext];
            }
        } finally {
            $zip->close();
        }

        throw new RuntimeException('Aucun fichier de catalogue lisible dans ce ZIP.');
    }

    /** @return array<int, array<int, string>> */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new RuntimeException("Impossible d'ouvrir le fichier.");
        }

        // Séparateur = celui qui apparaît le plus dans la 1re ligne.
        $first = $this->utf8((string) fgets($handle));
        rewind($handle);
        $delimiters = [',' => 0, ';' => 0, "\t" => 0, '|' => 0];
        foreach ($delimiters as $d => $_) {
            $delimiters[$d] = substr_count($first, $d);
        }
        arsort($delimiters);
        $delimiter = array_key_first($delimiters);

        $lines = [];
        while (($line = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $lines[] = array_map(fn ($v) => $this->utf8((string) $v), $line);

            if (count($lines) > self::MAX_ROWS + 10) {
                break;
            }
        }
        fclose($handle);

        return $lines;
    }

    private function readNative(string $path, string $ext): array
    {
        return match ($ext) {
            'csv', 'tsv' => $this->tableFromLines($this->readCsv($path)),
            'xlsx', 'ods' => $this->tableFromLines($this->readSpreadsheet(
                $ext === 'xlsx'
                    ? new \OpenSpout\Reader\XLSX\Reader()
                    : new \OpenSpout\Reader\ODS\Reader(),
                $path
            )),
            'json' => $this->readJson($path),
            'xml' => $this->readXml($path),
            default => throw new RuntimeException('Format de fichier non pris en charge.'),
        };
    }

    /** @return array<int, array<int, string>> */
    private function readSpreadsheet(ReaderInterface $reader, string $path): array
    {
        $lines = [];

        try {
            $reader->open($path);

            // Première feuille seulement.
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->toArray();
                    $max = $cells === [] ? 0 : max(array_keys($cells)) + 1;
                    $line = [];

                    for ($i = 0; $i < $max; $i++) {
                        $value = $cells[$i] ?? '';
                        $line[] = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : (string) $value;
                    }

                    $lines[] = $line;

                    if (count($lines) > self::MAX_ROWS + 10) {
                        break 2;
                    }
                }
                break;
            }
        } catch (\Throwable $e) {
            report($e);
            throw new RuntimeException('Fichier Excel/ODS illisible ou corrompu.');
        } finally {
            $reader->close();
        }

        return $lines;
    }

    private function readJson(string $path): array
    {
        $json = json_decode($this->utf8((string) file_get_contents($path)), true);

        if (! is_array($json)) {
            throw new RuntimeException('JSON invalide.');
        }

        return $this->tableFromList($json);
    }

    private function readXml(string $path): array
    {
        $xml = $this->utf8((string) file_get_contents($path));

        // Retire les préfixes d'espace de noms (<g:price> -> <price>) : flux Google Shopping.
        $xml = preg_replace('/<(\/?)[A-Za-z0-9_-]+:/', '<$1', $xml);
        $xml = preg_replace('/\sxmlns(:[A-Za-z0-9_-]+)?="[^"]*"/', '', $xml);

        // Pas de LIBXML_NOENT : protège des attaques XXE.
        $element = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);

        if (! $element) {
            throw new RuntimeException('XML invalide.');
        }

        return $this->tableFromList(json_decode(json_encode($element), true) ?? []);
    }

    /** Structure imbriquée (JSON/XML) -> table aplatie (tarification.prix_ht, ...). */
    private function tableFromList(array $data): array
    {
        $list = $this->findList($data);

        if ($list === null) {
            throw new RuntimeException('Aucune liste de produits trouvée.');
        }

        $rows = [];
        $headers = [];

        foreach ($list as $item) {
            if (! is_array($item)) {
                continue;
            }

            $row = [];
            $this->flatten($item, '', $row);

            $headers = array_values(array_unique([...$headers, ...array_keys($row)]));
            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    private function flatten(array $data, string $prefix, array &$out, int $depth = 0): void
    {
        foreach ($data as $key => $value) {
            $name = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value) && $value === []) {
                $out[$name] = '';
            } elseif (is_array($value) && array_is_list($value) && ! is_array($value[0] ?? null)) {
                $out[$name] = implode(', ', array_map('strval', $value)); // ["a","b"] -> "a, b"
            } elseif (is_array($value) && $depth < 3) {
                $this->flatten($value, $name, $out, $depth + 1);
            } else {
                $out[$name] = is_array($value)
                    ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : (is_bool($value) ? ($value ? 'true' : 'false') : (string) $value);
            }
        }
    }
    /** Plus grande liste d'objets, jusqu'à 3 niveaux (ex: {"data":{"products":[...]}}). */
    private function findList(array $data, int $depth = 0): ?array
    {
        $best = ($data !== [] && array_is_list($data) && is_array($data[0])) ? $data : null;

        if ($depth < 3) {
            foreach ($data as $value) {
                if (is_array($value) && ($found = $this->findList($value, $depth + 1))
                    && count($found) > count($best ?? [])) {
                    $best = $found;
                }
            }
        }

        return $best;
    }

    /** Lignes de cellules -> en-têtes + lignes associatives. */
    private function tableFromLines(array $lines): array
    {
        $lines = array_values(array_filter($lines, fn ($l) => trim(implode('', $l)) !== ''));

        if ($lines === []) {
            return ['headers' => [], 'rows' => []];
        }

        // Vraie ligne d'en-têtes = celle avec le plus de cellules texte parmi les 10 premières
        // (saute un titre ou un logo au-dessus du tableau).
        $headerAt = 0;
        $best = -1;
        foreach (array_slice($lines, 0, 10, true) as $i => $line) {
            $score = count(array_filter($line, fn ($c) => trim($c) !== '' && ! is_numeric(trim($c))));
            if ($score > $best) {
                $best = $score;
                $headerAt = $i;
            }
        }

        $headers = [];
        foreach ($lines[$headerAt] as $i => $name) {
            $name = trim($name);
            $name = $name !== '' ? $name : 'colonne_'.($i + 1);
            $headers[] = in_array($name, $headers, true) ? $name.'_'.($i + 1) : $name;
        }

        $rows = [];
        foreach (array_slice($lines, $headerAt + 1) as $line) {
            $row = [];
            foreach ($headers as $i => $name) {
                $row[$name] = trim($line[$i] ?? '');
            }
            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /** Retire le BOM, convertit Windows-1252 (CSV Excel français) en UTF-8. */
    private function utf8(string $text): string
    {
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);

        return mb_check_encoding($text, 'UTF-8')
            ? $text
            : mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
    }
}