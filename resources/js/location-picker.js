import L from 'leaflet';
import './leaflet-default-icon.js';

function setInputValue(input, value) {
    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
}

export function initLocationPicker(container) {
    const addressInput = container.querySelector('[data-role="address-input"]');
    const mapEl = container.querySelector('[data-role="map"]');
    const latInput = container.querySelector('[data-role="latitude"]');
    const lngInput = container.querySelector('[data-role="longitude"]');
    const suggestionsEl = container.querySelector('[data-role="suggestions"]');
    let loadingIndicator = null;

    if (!addressInput || !mapEl || !latInput || !lngInput) {
        console.warn('location-picker: éléments manquants dans le conteneur', container);
        return;
    }
    if (mapEl._leaflet_id) {
        return;
    }
    const startLat = parseFloat(latInput.value) || 47.322047;
    const startLng = parseFloat(lngInput.value) || 5.04148;

    const map = L.map(mapEl).setView([startLat, startLng], latInput.value ? 15 : 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    let marker = latInput.value
        ? L.marker([startLat, startLng], { draggable: true }).addTo(map)
        : null;

    function showLoading() {
        if (loadingIndicator) return;
        loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'absolute inset-0 z-[1000] flex items-center justify-center bg-white/70';
        loadingIndicator.innerHTML = `<svg class="animate-spin h-6 w-6 text-[#1E3D59]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>`;
        mapEl.style.position = 'relative';
        mapEl.appendChild(loadingIndicator);
    }

    function hideLoading() {
        loadingIndicator?.remove();
        loadingIndicator = null;
    }

    async function reverseGeocode(lat, lng) {
        const submitButton = container.closest('form')?.querySelector('[type="submit"]');
        if (submitButton) submitButton.disabled = true;
        showLoading();

        try {
            const response = await fetch(`/api/geocode/reverse?lat=${lat}&lon=${lng}`);
            const result = await response.json();
            if (result && result.display_name) setInputValue(addressInput, result.display_name);
        } catch (error) {
            console.error('Erreur de géocodage inverse', error);
        } finally {
            if (submitButton) submitButton.disabled = false;
            hideLoading();
        }
    }

    function clearPosition() {
        setInputValue(latInput, '');
        setInputValue(lngInput, '');

        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
    }

    function setPosition(lat, lng, { skipReverseGeocode = false } = {}) {
        setInputValue(latInput, lat);
        setInputValue(lngInput, lng);

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', () => {
                const pos = marker.getLatLng();
                setPosition(pos.lat, pos.lng);
            });
        }

        map.setView([lat, lng], 15);

        if (!skipReverseGeocode) {
            reverseGeocode(lat, lng);
        }
    }

    map.on('click', (e) => {
        setPosition(e.latlng.lat, e.latlng.lng);
    });

        function hideSuggestions() {
            clearTimeout(debounceTimer);
            suggestionsEl.innerHTML = '';
            suggestionsEl.classList.add('hidden');
        }

    let debounceTimer;
    addressInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = addressInput.value.trim();
        if (query.length === 0) clearPosition();
        if (query.length < 3) { hideSuggestions(); return; }

        debounceTimer = setTimeout(async () => {
            try {
                const response = await fetch(`/api/geocode/search?q=${encodeURIComponent(query)}`);
                const results = await response.json();
                if (document.activeElement !== addressInput) return;
                renderSuggestions(results);
            } catch (error) {
                console.error('Erreur de géocodage', error);
            }
        }, 400);
    });

    // Perte de focus ou Échap : on ferme la liste.
    addressInput.addEventListener('blur', hideSuggestions);
    addressInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideSuggestions();
    });

    // Empêche le blur du champ quand on clique une suggestion (sinon la liste disparaît avant le clic).
    suggestionsEl.addEventListener('mousedown', (e) => e.preventDefault());

    function renderSuggestions(results) {
        suggestionsEl.innerHTML = '';

        if (results.length === 0) {
            suggestionsEl.classList.add('hidden');
            return;
        }

        results.forEach((result) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'w-full text-left px-4 py-2 text-sm text-[#333333] hover:bg-[#FDFBF7] transition';
            item.textContent = result.display_name;
            item.addEventListener('click', () => {
                setInputValue(addressInput, result.display_name);
                setPosition(parseFloat(result.lat), parseFloat(result.lon), { skipReverseGeocode: true });
                hideSuggestions();
            });
            suggestionsEl.appendChild(item);
        });

        suggestionsEl.classList.remove('hidden');
    }

}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-location-picker]').forEach(initLocationPicker);
});

document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('[data-location-picker]').forEach(initLocationPicker);
});