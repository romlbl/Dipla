// Store partagé (header, accueil, recherche) : adresse choisie pour les recherches.
// Persisté en localStorage ; toutes les lectures/écritures sont protégées
// (navigation privée, stockage bloqué).

const STORAGE_KEY = 'dipla-search-location';

function readSaved() {
    try {
        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
        return saved && saved.lat && saved.lng ? saved : null;
    } catch (error) {
        return null;
    }
}

function writeSaved(value) {
    try {
        if (value) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
        } else {
            localStorage.removeItem(STORAGE_KEY);
        }
    } catch (error) {
        // Stockage indisponible : la position reste valable pour la session.
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.store('searchLocation', {
        label: null,
        lat: null,
        lng: null,

        init() {
            // Juste après connexion, l'adresse du compte écrase la position en mémoire.
            if (!window.diplaJustLoggedIn) {
                const saved = readSaved();

                if (saved) {
                    this.label = saved.label;
                    this.lat = saved.lat;
                    this.lng = saved.lng;
                    return;
                }
            }

            const userLocation = window.diplaUserLocation;
            if (userLocation && userLocation.lat && userLocation.lng) {
                this.set(userLocation.label, userLocation.lat, userLocation.lng);
            }
        },

        set(label, lat, lng) {
            this.label = label;
            this.lat = lat;
            this.lng = lng;
            writeSaved({ label, lat, lng });
        },

        clear() {
            this.label = null;
            this.lat = null;
            this.lng = null;
            writeSaved(null);
        },

        get hasLocation() {
            return this.lat !== null && this.lng !== null;
        },
    });
});