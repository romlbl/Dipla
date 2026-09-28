<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeocodeController extends Controller
{
    protected function key(): string
    {
        return config('services.locationiq.key');
    }

    // Recherche adresse -> suggestions
    public function search(Request $request)
    {
        $q = mb_substr(trim($request->query('q', '')), 0, 150);

        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        $cacheKey = 'geocode:search:' . md5($q);

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        try {
            $response = Http::timeout(4)->get('https://us1.locationiq.com/v1/autocomplete', [
                'key' => $this->key(),
                'q' => $q,
                'format' => 'json',
                'limit' => 5,
            ]);
        } catch (\Throwable $e) {
            return response()->json([]);
        }

        if (! $response->successful()) {
            return response()->json([]);
        }

        $results = $response->json();
        Cache::put($cacheKey, $results, now()->addDay());

        return response()->json($results);
    }

    // Coordonnées -> adresse
    public function reverse(Request $request)
    {
        $lat = (float) $request->query('lat');
        $lon = (float) $request->query('lon');

        if (abs($lat) > 90 || abs($lon) > 180) {
            return response()->json(['error' => 'coordonnées invalides'], 422);
        }

        if (!$this->key()) {
            \Log::error('LocationIQ: clé API manquante');
            return response()->json(['error' => 'service indisponible'], 503);
        }

        $cacheKey = 'geocode:reverse:' . round($lat, 5) . ':' . round($lon, 5);

        try {
            $result = Cache::remember($cacheKey, now()->addDay(), function () use ($lat, $lon) {
                $response = Http::timeout(5)->get('https://us1.locationiq.com/v1/reverse', [
                    'key' => $this->key(),
                    'lat' => $lat,
                    'lon' => $lon,
                    'format' => 'json',
                ]);

                if (!$response->successful()) {
                    \Log::warning('LocationIQ reverse a échoué', ['status' => $response->status(), 'body' => $response->body()]);
                    return null;
                }

                return $response->json();
            });
        } catch (\Throwable $e) {
            \Log::error('LocationIQ reverse exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'service indisponible'], 503);
        }

        return response()->json($result);
    }

    // Itinéraire entre 2 points
    public function route(Request $request, string $mode)
    {
        $profiles = ['walking' => 'walking', 'cycling' => 'cycling', 'driving' => 'driving'];
        $profile = $profiles[$mode] ?? null;

        if (!$profile) {
            return response()->json(['error' => 'mode invalide'], 422);
        }

        $originLat = (float) $request->query('origin_lat');
        $originLng = (float) $request->query('origin_lng');
        $destLat = (float) $request->query('dest_lat');
        $destLng = (float) $request->query('dest_lng');

        $coords = "{$originLng},{$originLat};{$destLng},{$destLat}";

        try {
            $response = Http::timeout(5)->get("https://us1.locationiq.com/v1/directions/{$profile}/{$coords}", [
                'key' => $this->key(),
                'overview' => 'full',
                'geometries' => 'geojson',
            ]);

            if (!$response->successful()) {
                \Log::warning('LocationIQ directions a échoué', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['routes' => []]);
            }

            return response()->json($response->json());
        } catch (\Throwable $e) {
            \Log::error('LocationIQ directions exception', ['message' => $e->getMessage()]);
            return response()->json(['routes' => []]);
        }
    }
}