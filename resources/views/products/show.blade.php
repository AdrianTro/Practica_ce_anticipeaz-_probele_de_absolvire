@extends('layouts.app')

@section('title', $product->name.' | ReclamDesign Modern')

@section('content')
@php
    $mainImage = $product->mainImagePath();
    $backImage = $product->secondImagePath();
    $mainCarouselImages = $product->localizedCarouselImages($mainImage);
    $mainHasLocalizedCarouselImage = count(array_unique($mainCarouselImages)) > 1;
    $categoryLabel = trim(($product->category?->name ?? 'Fara categorie').' / '.($product->subcategory?->name ?? ''), ' /');
    $payload = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) $product->price,
        'basePrice' => (float) $product->price,
        'category' => $categoryLabel,
        'image' => \App\Support\StoredImage::url($mainImage),
        'url' => route('products.show', $product),
    ];
    $isWearable = $product->isWearableCustomizable();
    $isMug = $product->isMugCustomizable();
    $mugModel = $product->attributes['model'] ?? (($product->type === 'termo') ? 'model/cana/cana_termo.glb' : 'model/cana/cana.glb');
    $sizes = collect(explode(',', (string) $product->size))->map(fn ($value) => trim($value))->filter()->values();
    $colors = collect(explode(',', (string) $product->color))->map(fn ($value) => trim($value))->filter()->values();
    $customSpecs = collect($product->attributes['custom_features'] ?? [])
        ->filter()
        ->map(fn ($value, $key) => [
            'label' => \App\Support\SubcategoryFeatures::labelFor($key, $product->subcategory?->features[$key] ?? null),
            'value' => $value,
        ]);
@endphp

<section class="section-shell product-detail">
    <div class="gallery-panel">
        <div class="main-product-image">
            <img
                id="main-product-image"
                src="{{ \App\Support\StoredImage::url($mainImage) }}"
                @if($mainHasLocalizedCarouselImage)
                    data-carousel-image
                    data-image-ro="{{ \App\Support\StoredImage::url($mainCarouselImages['ro']) }}"
                    data-image-ru="{{ \App\Support\StoredImage::url($mainCarouselImages['ru']) }}"
                    data-image-en="{{ \App\Support\StoredImage::url($mainCarouselImages['en']) }}"
                @endif
                alt="{{ $product->name }}"
            >
        </div>
        @if($product->images->count() > 1)
            <div class="thumb-row">
                @foreach($product->images as $image)
                    @php
                        $thumbCarouselImages = $product->localizedCarouselImages($image->path);
                        $thumbHasLocalizedCarouselImage = count(array_unique($thumbCarouselImages)) > 1;
                    @endphp
                    <button
                        class="thumb-button"
                        type="button"
                        data-image="{{ \App\Support\StoredImage::url($image->path) }}"
                        @if($thumbHasLocalizedCarouselImage)
                            data-image-ro="{{ \App\Support\StoredImage::url($thumbCarouselImages['ro']) }}"
                            data-image-ru="{{ \App\Support\StoredImage::url($thumbCarouselImages['ru']) }}"
                            data-image-en="{{ \App\Support\StoredImage::url($thumbCarouselImages['en']) }}"
                        @endif
                    >
                        <img
                            src="{{ \App\Support\StoredImage::url($image->path) }}"
                            @if($thumbHasLocalizedCarouselImage)
                                data-carousel-image
                                data-image-ro="{{ \App\Support\StoredImage::url($thumbCarouselImages['ro']) }}"
                                data-image-ru="{{ \App\Support\StoredImage::url($thumbCarouselImages['ru']) }}"
                                data-image-en="{{ \App\Support\StoredImage::url($thumbCarouselImages['en']) }}"
                            @endif
                            alt="Imagine {{ $loop->iteration }}"
                        >
                    </button>
                @endforeach
            </div>
        @endif

        @if($isWearable)
            <div class="customizer-card wearable-customizer" data-wearable-customizer data-front-image="{{ \App\Support\StoredImage::url($mainImage) }}" data-back-image="{{ \App\Support\StoredImage::url($backImage) }}" data-print-areas='@json($product->wearablePrintAreas())'>
                <div class="customizer-head">
                    <span>👕 Design față / spate</span>
                    <h2>Modifica imaginea pe {{ mb_strtolower($product->name) }}</h2>
                    <p>Adauga maximum 4 imagini. Fiecare imagine poate fi selectata, miscata si marita. Fiecare imagine adauga 15 MDL la pret.</p>
                </div>
                <div class="wearable-stage" id="wearable-stage" data-current-side="front">
                    <span class="stage-side-label" id="stage-side-label">Față</span>
                    <img class="wearable-base" id="wearable-base" src="{{ \App\Support\StoredImage::url($mainImage) }}" alt="{{ $product->name }} baza">
                    <div class="wearable-overlays" id="wearable-overlays"></div>
                </div>
                <div class="customizer-tools customizer-tools-grid">
                    <button class="primary-btn" type="button" data-design-add>Modifica</button>
                    <input id="wearable-upload" type="file" accept="image/*" multiple hidden>
                    <button class="secondary-btn" type="button" data-design-center>Centreaza</button>
                    <button class="secondary-btn" type="button" data-side-toggle>Spate</button>
                    <button class="secondary-btn danger-lite" type="button" data-design-remove>Șterge imaginea</button>
                    <label class="range-label">Marime imagine selectata
                        <input id="wearable-size" type="range" min="60" max="360" value="150">
                    </label>
                    <label class="range-label">Rotirea imaginei selectate
                        <input id="wearable-rotation" type="range" min="-180" max="180" value="0">
                    </label>
                    <span class="tool-note" id="design-counter">0 / 4 imagini</span>
                </div>
            </div>
        @endif

        @if($isMug)
            <div class="customizer-card mug-customizer" data-mug-customizer>
                <div class="customizer-head">
                    <span>☕ Cana 3D</span>
                    <h2>Textura pe modelul 3D</h2>
                    <p>Incarca imaginea, apoi roteste si apropie modelul cu mouse-ul sau touch. Imaginea personalizata se salveaza in comanda.</p>
                </div>
                <div 
    class="mug-viewer" 
    data-mug-viewer 
    data-model="{{ asset($mugModel) }}"
    data-reference-model="{{ asset('model/cana/cana.glb') }}"
>
                    <div class="viewer-loader">Se incarca modelul 3D...</div>
                </div>
                <div class="customizer-tools">
                    <label class="file-label">
    Upload textura
    <input id="mug-texture" type="file" accept="image/*">
</label>

<label class="range-label">
    Marime imagine
    <input id="mug-image-scale" type="range" min="1" max="4" step="0.05" value="1.5">
</label>
<span class="tool-note">Imaginea poate fi marita si miscata pe cana.</span>
                </div>
            </div>
        @endif
    </div>

    <aside class="product-side" data-product="{{ e(json_encode($payload, JSON_UNESCAPED_UNICODE)) }}">
        <span class="eyebrow">{{ $product->category?->icon }} {{ $categoryLabel }}</span>
        <h1>{{ $product->name }}</h1>
        <p class="detail-description">{{ $product->description }}</p>

        <div class="price-box">
            <span>Pret</span>
            <strong id="detail-price" data-base-price="{{ (float) $product->price }}">{{ number_format((float) $product->price, 2) }} MDL</strong>
            <small id="design-price-note" hidden>Personalizare: +0 MDL</small>
        </div>

        <div class="product-option-box">
            @if($sizes->isNotEmpty())
                <label>Marime
                    <select id="selected-size" data-product-option="selected_size">
                        @foreach($sizes as $size)
                            <option value="{{ $size }}">{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            @if($colors->isNotEmpty())
                <label>Culoare
                    <select id="selected-color" data-product-option="selected_color">
                        @foreach($colors as $color)
                            <option value="{{ $color }}">{{ $color }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
        </div>

        <div class="spec-grid">
            @admin
                <div><span>Stoc</span><strong>{{ $product->stock }}</strong></div>
            @endadmin
            @if($product->subcategory)<div><span>Subcategorie</span><strong>{{ $product->subcategory->name }}</strong></div>@endif
            @if($product->size)<div><span>Marime</span><strong>{{ $product->size }}</strong></div>@endif
            @if($product->color)<div><span>Culoare</span><strong>{{ $product->color }}</strong></div>@endif
            <div class="modification-spec" id="modification-spec" hidden><span>Modificat</span><strong>—</strong></div>
            @if($product->dimensions)<div><span>Dimensiuni</span><strong>{{ $product->dimensions }}</strong></div>@endif
            @if($product->volume)<div><span>Volum</span><strong>{{ $product->volume }}</strong></div>@endif
            @if($product->type)<div><span>Tip</span><strong>{{ $product->type }}</strong></div>@endif
            @foreach($customSpecs as $spec)
                <div><span>{{ $spec['label'] }}</span><strong>{{ $spec['value'] }}</strong></div>
            @endforeach
        </div>

        <button class="primary-btn wide add-cart" type="button" data-add-cart>Adauga in cos 🛒 +</button>
        <a class="secondary-btn wide" href="{{ route('cart.index') }}">Mergi la cos</a>

        @admin
            <div class="admin-product-actions">
                <a class="secondary-btn" href="{{ route('admin.products.edit', $product) }}">✏️ Editeaza produs</a>
            </div>
        @endadmin
    </aside>
</section>

@if($related->isNotEmpty())
<section class="section-shell products-section">
    <div class="section-heading">
        <span>Produse similare</span>
        <h2>Din aceeasi selectie</h2>
    </div>
    <div class="product-grid small-grid">
        @foreach($related as $product)
            @include('partials.product-card', ['product' => $product])
        @endforeach
    </div>
</section>
@endif
@endsection

@if($isMug)
@push('scripts')
<script type="importmap">
{
  "imports": {
    "three": "https://unpkg.com/three@0.152.2/build/three.module.js",
    "three/addons/": "https://unpkg.com/three@0.152.2/examples/jsm/"
  }
}
</script>

<script type="module">
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

const holder = document.querySelector('[data-mug-viewer]');
const textureInput = document.getElementById('mug-texture');
const mugImageScale = document.getElementById('mug-image-scale');

let activeMugTexture = null;
window.ReclamMugState = window.ReclamMugState || {};
const mugTextureState = {
    scale: 1,
    offsetX: 0,
    offsetY: 0
};

const dragState = {
    active: false,
    startX: 0,
    startY: 0,
    startOffsetX: 0,
    startOffsetY: 0
};

const fileToDataUrl = (file) => new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
});
function updateMugTextureTransform() {
    if (!activeMugTexture) return;

    mugTextureState.scale = parseFloat(mugImageScale?.value || 1);

    const repeatValue = 1 / mugTextureState.scale;

    activeMugTexture.repeat.set(repeatValue, repeatValue);

    activeMugTexture.offset.set(
        0.5 - repeatValue / 2 + mugTextureState.offsetX,
        0.5 - repeatValue / 2 + mugTextureState.offsetY
    );

    activeMugTexture.needsUpdate = true;
}
if (holder) {
    holder.style.minHeight = '420px';

    const width = holder.clientWidth || 500;
    const height = 420;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
    camera.position.set(0, 1.5, 5);

    const renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true,
        preserveDrawingBuffer: true
    });

    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;

    holder.innerHTML = '';
    holder.appendChild(renderer.domElement);
    window.ReclamMugState.canvas = renderer.domElement;

    scene.add(new THREE.AmbientLight(0xffffff, 1.4));

    const light1 = new THREE.DirectionalLight(0xffffff, 2.5);
    light1.position.set(4, 5, 6);
    scene.add(light1);

    const light2 = new THREE.DirectionalLight(0xffffff, 1.5);
    light2.position.set(-4, 3, -4);
    scene.add(light2);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.minDistance = 2;
    controls.maxDistance = 10;

    let loadedModel = null;
    const loader = new GLTFLoader();
    const raycaster = new THREE.Raycaster();
const pointer = new THREE.Vector2();

function setPointerFromEvent(event) {
    const rect = renderer.domElement.getBoundingClientRect();

    pointer.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    pointer.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
}
   const BASE_MODEL_SIZE = 2.8;

function getModelBoxData(object) {
    const box = new THREE.Box3().setFromObject(object);
    const size = new THREE.Vector3();
    const center = new THREE.Vector3();

    box.getSize(size);
    box.getCenter(center);

    return { box, size, center };
}

function normalizeModelToReference(model, referenceHeight) {
    const { size, center } = getModelBoxData(model);

    model.position.sub(center);

    const scale = referenceHeight / size.y;
    model.scale.setScalar(scale);

    model.traverse((child) => {
        if (!child.isMesh) return;

        child.material = child.material.clone();
        child.material.side = THREE.FrontSide;
        child.material.needsUpdate = true;
    });

    return model;
}

function loadReferenceHeight(callback) {
    const referenceModelUrl = holder.dataset.referenceModel;

    loader.load(referenceModelUrl, (referenceGltf) => {
        const referenceModel = referenceGltf.scene;

        const { size } = getModelBoxData(referenceModel);
        const maxDim = Math.max(size.x, size.y, size.z);

        const referenceScale = BASE_MODEL_SIZE / maxDim;
        const referenceHeight = size.y * referenceScale;

        callback(referenceHeight);
    });
}

loadReferenceHeight((referenceHeight) => {
    loader.load(
        holder.dataset.model,
        (gltf) => {
            loadedModel = normalizeModelToReference(gltf.scene, referenceHeight);
            scene.add(loadedModel);
        },
        undefined,
        (error) => {
            holder.innerHTML = '<div class="viewer-loader">Modelul 3D nu a putut fi incarcat.</div>';
            console.error('Eroare GLB:', error);
        }
        
    );
});
let mugWrap = null;

const isThermoModel = holder.dataset.model.includes('cana_termo');

const wrapRotationOffsetSimple = Math.PI * -0.001;
const wrapRotationOffsetThermo = Math.PI * -0.001;
    textureInput?.addEventListener('change', async (event) => {
        const file = event.target.files?.[0];
        if (!file || !loadedModel) return;

        window.ReclamMugState.texture = await fileToDataUrl(file);
        const url = URL.createObjectURL(file);
new THREE.TextureLoader().load(url, (texture) => {
    texture.colorSpace = THREE.SRGBColorSpace;

    if (mugWrap) {
        scene.remove(mugWrap);
        mugWrap.geometry.dispose();
        mugWrap.material.dispose();
    }

  const modelBox = new THREE.Box3().setFromObject(loadedModel);
const modelSize = new THREE.Vector3();
const modelCenter = new THREE.Vector3();

modelBox.getSize(modelSize);
modelBox.getCenter(modelCenter);

const isThermoModel = holder.dataset.model.includes('cana_termo');

// valori separate pentru cana termo si cana simpla
const wrapHeight = modelSize.y * (isThermoModel ? 0.90 : 0.90);
const wrapRadius = modelSize.z * (isThermoModel ? 0.36 : 0.365);

// taietura de langa maner
const gapAngle = Math.PI * (isThermoModel ? 0.2 : 0.2); 

const geometry = new THREE.CylinderGeometry(
    wrapRadius,
    wrapRadius,
    wrapHeight,
    128,
    1,
    true,
    -Math.PI + gapAngle / 2,
    Math.PI * 2 - gapAngle
);

   texture.wrapS = THREE.RepeatWrapping;
texture.wrapT = THREE.RepeatWrapping;

    activeMugTexture = texture;
    updateMugTextureTransform();

    const material = new THREE.MeshBasicMaterial({
        map: texture,
        transparent: true,
        opacity: 1,
        side: THREE.DoubleSide,
        depthWrite: false
    });

mugWrap = new THREE.Mesh(geometry, material);

mugWrap.position.set(
    modelCenter.x,
    modelCenter.y,
    modelCenter.z + (isThermoModel ? 0.433 : 0.4)
);

mugWrap.rotation.y = isThermoModel 
    ? wrapRotationOffsetThermo 
    : wrapRotationOffsetSimple;
    mugWrap.renderOrder = 999;

    scene.add(mugWrap);

    document.dispatchEvent(new CustomEvent('reclam:mug-texture-added'));
});
    });
renderer.domElement.addEventListener('pointerdown', (event) => {
    if (!mugWrap || !activeMugTexture) return;

    setPointerFromEvent(event);
    raycaster.setFromCamera(pointer, camera);

    const hits = raycaster.intersectObject(mugWrap, false);
    if (!hits.length) return;

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    dragState.active = true;
    dragState.startX = event.clientX;
    dragState.startY = event.clientY;
    dragState.startOffsetX = mugTextureState.offsetX;
    dragState.startOffsetY = mugTextureState.offsetY;

    controls.enabled = false;
    renderer.domElement.setPointerCapture(event.pointerId);
    renderer.domElement.style.cursor = 'grabbing';
}, true);
window.addEventListener('pointermove', (event) => {
    if (!dragState.active) return;

    const dx = ((event.clientX - dragState.startX) / renderer.domElement.clientWidth) * 1.8;
    const dy = ((event.clientY - dragState.startY) / renderer.domElement.clientHeight) * 1.8;

    mugTextureState.offsetX = dragState.startOffsetX - dx;
    mugTextureState.offsetY = dragState.startOffsetY + dy;

    const repeatValue = 1 / mugTextureState.scale;
    const limit = Math.max(0, (1 - repeatValue) / 2);

    mugTextureState.offsetX = Math.max(-limit, Math.min(limit, mugTextureState.offsetX));
    mugTextureState.offsetY = Math.max(-limit, Math.min(limit, mugTextureState.offsetY));

    updateMugTextureTransform();
});

window.addEventListener('pointerup', (event) => {
    if (!dragState.active) return;

    dragState.active = false;
    controls.enabled = true;

    try {
        renderer.domElement.releasePointerCapture(event.pointerId);
    } catch (e) {}

    renderer.domElement.style.cursor = 'grab';
});
mugImageScale?.addEventListener('input', updateMugTextureTransform);

  function animate() {
    requestAnimationFrame(animate);

    if (loadedModel) {

       if (mugWrap) {
mugWrap.rotation.y = loadedModel.rotation.y + (
    isThermoModel 
        ? wrapRotationOffsetThermo 
        : wrapRotationOffsetSimple
);
}
    }

    controls.update();
    renderer.render(scene, camera);
}

    animate();
}

</script>
@endpush
@endif
