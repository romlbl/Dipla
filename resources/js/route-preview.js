import L from 'leaflet';
import './leaflet-default-icon.js';

// Serveur gratuit avec un vrai profil par mode (le serveur OSRM démo = voiture seulement).
const PROFILES = {
    walking: 'routed-foot',
    cycling: 'routed-bike',
    driving: 'routed-car',
};

const MODE_COLORS = { walking: '#1E3D59', cycling: '#4A3B5C', driving: '#333333' };

function formatDuration(seconds) {
    const minutes = Math.round(seconds / 60);
    if (minutes < 1) return '< 1 min';
    if (minutes < 60) return `${minutes} min`;
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return m === 0 ? `${h} h` : `${h} h ${m}`;
}

// Composant Alpine pour l'itinéraire : position du user (store "searchLocation"
// en priorité, sinon géolocalisation navigateur), puis un tracé réel par mode.
document.addEventListener('alpine:init', () => {
    Alpine.data('routePreview', (destLat, destLng) => ({
        destLat,
        destLng,
        mode: 'walking', // mode par défaut
        loading: true,
        error: null,
        distanceKm: null,
        durations: { walking: null, cycling: null, driving: null },
        routes: { walking: null, cycling: null, driving: null },
        originLat: null,
        originLng: null,
        map: null,
        polyline: null,

        init() {
            this.resolveOrigin();
        },

        resolveOrigin() {
            if (this.$store.searchLocation.hasLocation) {
                this.originLat = this.$store.searchLocation.lat;
                this.originLng = this.$store.searchLocation.lng;
                this.initMapAndRoute();
                return;
            }

            if (!navigator.geolocation) {
                this.loading = false;
                this.error = "Activez votre position pour voir l'itinéraire.";
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.originLat = position.coords.latitude;
                    this.originLng = position.coords.longitude;
                    // Store partagé : pas de nouvelle demande à chaque fiche consultée.
                    this.$store.searchLocation.set('Ma position actuelle', this.originLat, this.originLng);
                    this.initMapAndRoute();
                },
                () => {
                    this.loading = false;
                    this.error = "Activez votre position pour voir l'itinéraire.";
                },
                { timeout: 8000 }
            );
        },

        initMapAndRoute() {
            this.$nextTick(() => {
                const mapEl = this.$refs.map;
                if (!mapEl || mapEl._leaflet_id) return;

                this.map = L.map(mapEl).setView([this.destLat, this.destLng], 12);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                    maxZoom: 19,
                }).addTo(this.map);

                L.marker([this.destLat, this.destLng]).addTo(this.map).bindPopup('Destination');
                L.marker([this.originLat, this.originLng]).addTo(this.map).bindPopup('Vous');

                this.fetchRoutes();
            });
        },

        async fetchRoute(mode) {
            const url = `/api/geocode/route/${mode}`
                + `?origin_lat=${this.originLat}&origin_lng=${this.originLng}`
                + `&dest_lat=${this.destLat}&dest_lng=${this.destLng}`;

            try {
                const response = await fetch(url);
                const data = await response.json();
                return data.routes?.[0] ?? null;
            } catch (error) {
                console.error(`Erreur itinéraire (${mode})`, error);
                return null;
            }
        },

        async fetchRoutes() {
            this.loading = true;
            this.error = null;

            // Les 3 modes en parallèle : temps réels pour chaque bouton.
            const modes = Object.keys(PROFILES);
            const results = await Promise.all(modes.map((mode) => this.fetchRoute(mode)));

            modes.forEach((mode, i) => {
                this.routes[mode] = results[i];
                this.durations[mode] = results[i] ? formatDuration(results[i].duration) : null;
            });

            this.loading = false;
            this.showMode();
        },

        showMode() {
            const route = this.routes[this.mode];

            if (this.polyline) {
                this.map.removeLayer(this.polyline);
                this.polyline = null;
            }

            if (!route) {
                this.distanceKm = null;
                this.error = "Itinéraire indisponible pour ce mode de transport.";
                return;
            }

            this.error = null;
            this.distanceKm = route.distance / 1000;

            const coords = route.geometry.coordinates.map(([lng, lat]) => [lat, lng]);
            this.polyline = L.polyline(coords, { color: MODE_COLORS[this.mode], weight: 4 }).addTo(this.map);
            this.map.fitBounds(coords);
        },

        setMode(mode) {
            this.mode = mode;
            if (this.map && !this.loading) this.showMode();
        },
    }));
});