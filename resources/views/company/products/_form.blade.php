@csrf

@if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 mb-4">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<div class="flex flex-col gap-4">
    <div>
        <label for="title" class="block text-sm font-medium text-[#1E293B] mb-1">Nom du produit / service</label>
        <input type="text" name="title" id="title" required
               value="{{ old('title', $product->title ?? '') }}"
               class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
    </div>

    <div class="flex gap-3">
        <label class="flex-1">
            <input type="radio" name="type" value="produit" class="peer sr-only"
                   {{ old('type', $product->type ?? 'produit') === 'produit' ? 'checked' : '' }}>
            <span class="flex items-center justify-center rounded-full border border-[#E2E8F0] px-4 py-2 text-sm font-medium text-[#1E293B] cursor-pointer transition peer-checked:bg-[#1E3D59] peer-checked:text-[#FDFBF7] peer-checked:border-[#1E3D59]">
                Produit
            </span>
        </label>
        <label class="flex-1">
            <input type="radio" name="type" value="service" class="peer sr-only"
                   {{ old('type', $product->type ?? '') === 'service' ? 'checked' : '' }}>
            <span class="flex items-center justify-center rounded-full border border-[#E2E8F0] px-4 py-2 text-sm font-medium text-[#1E293B] cursor-pointer transition peer-checked:bg-[#1E3D59] peer-checked:text-[#FDFBF7] peer-checked:border-[#1E3D59]">
                Service
            </span>
        </label>
    </div>

    @php
        // Coché seulement si édition d'un produit déjà en prix variable. Jamais coché à la création.
        $isVariablePrice = old('price_variable')
            ? true
            : (isset($product) && $product->exists && is_null($product->price));
    @endphp
    <div x-data="{ variable: {{ $isVariablePrice ? 'true' : 'false' }} }">
        <label class="flex cursor-pointer items-center gap-2.5 mb-2 text-sm text-[#1E293B]">
            <input type="checkbox" x-model="variable" name="price_variable" value="1"
                class="h-4 w-4 cursor-pointer rounded border-[#E2E8F0] accent-[#1E3D59]">
            Prix variable 
        </label>

        <div x-show="!variable">
            <label for="price" class="block text-sm font-medium text-[#1E293B] mb-1">Prix (€)</label>
            <input type="number" name="price" id="price" step="0.01" min="0"
                :required="!variable" x-bind:disabled="variable"
                value="{{ old('price', $product->price ?? '') }}"
                class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm font-mono text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
            @error('price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-[#1E293B] mb-1">Description</label>
        <x-rich-textarea name="description" id="description" rows="5" required>{{ old('description', $product->description ?? '') }}</x-rich-textarea>
    </div>

    <div>
        <label for="keywords" class="block text-sm font-medium text-[#1E293B] mb-1">Mots-clés</label>
        <input type="text" name="keywords" id="keywords" placeholder="ex : artisanal, local, fait main"
               value="{{ old('keywords', $product->keywords ?? '') }}"
               class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
        <p class="text-xs text-[#333333]/60 mt-1">Séparés par des virgules, ça aide les clients à vous trouver.</p>
    </div>

    <div data-location-picker class="relative">
        <label for="address" class="block text-sm font-medium text-[#1E293B] mb-1">Adresse</label>

        <input type="text" name="address" id="address" required autocomplete="off"
            data-role="address-input"
            placeholder="Commence à taper une adresse..."
            value="{{ old('address', $product->address ?? auth('company')->user()->address) }}"
            class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">

        <div data-role="suggestions" style="z-index: 9999;"
            class="hidden absolute z-10 mt-1 w-full rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-lg max-h-60 overflow-y-auto">
        </div>

        <div data-role="map" class="mt-3 h-56 w-full rounded-xl border border-[#E2E8F0] overflow-hidden"></div>

        <p class="text-xs text-[#333333]/60 mt-1">Ajuste le repère sur la carte si besoin, ou glisse-le à la bonne position.</p>

        <input type="hidden" name="latitude" data-role="latitude" value="{{ old('latitude', $product->latitude ?? auth('company')->user()->latitude) }}">
        <input type="hidden" name="longitude" data-role="longitude" value="{{ old('longitude', $product->longitude ?? auth('company')->user()->longitude) }}">
    </div>


</div>