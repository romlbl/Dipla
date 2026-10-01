import { NeatGradient } from '@firecms/neat';

let gradientInstance = null;

function initNeatBackground() {
    const canvas = document.getElementById('neat-home-background');
    if (!canvas) return;

    // Évite une double init si l'événement se déclenche deux fois sur la même page
    if (canvas.dataset.neatInitialized === 'true') return;
    canvas.dataset.neatInitialized = 'true';

    gradientInstance = new NeatGradient({
        ref: canvas,
        colors: [
            { color: '#FDFBF7', enabled: true },
            { color: '#FDEEE2', enabled: true },
            { color: '#FAF3E0', enabled: true },
            { color: '#F5EFE6', enabled: true },
            { color: '#F1F5F9', enabled: true },
            { color: '#B8D4E6', enabled: false },
        ],


    speed: 2,
    horizontalPressure: 5,
    verticalPressure: 6,
    waveFrequencyX: 1,
    waveFrequencyY: 2,
    waveAmplitude: 10,
    secondaryWaveEnabled: false,
    secondaryWaveFrequencyX: 3,
    secondaryWaveFrequencyY: 3,
    secondaryWaveAmplitude: 5,
    secondaryWaveSpeed: 0.6,
    secondaryWaveAngle: 1,
    shadows: 0,
    highlights: 7,
    colorBrightness: 1.05,
    colorSaturation: 0,
    wireframe: false,
    antialias: false,
    colorBlending: 9,
    backgroundColor: '#ffffff',
    backgroundAlpha: 1,
    grainScale: 0,
    grainSparsity: 0,
    grainIntensity: 0,
    grainSpeed: 0,
    resolution: 1,
    yOffset: 0,
    yOffsetWaveMultiplier: 6.5,
    yOffsetColorMultiplier: 5,
    yOffsetFlowMultiplier: 3,
    flowDistortionA: 0.4,
    flowDistortionB: 3,
    flowScale: 3.3,
    flowEase: 0.53,
    flowEnabled: false,
    enableProceduralTexture: false,
    transparentTextureVoid: false,
    textureMode: 'bitmap',
    bakeEdgeSoftness: 1,
    textureVoidLikelihood: 0.06,
    textureVoidWidthMin: 10,
    textureVoidWidthMax: 500,
    textureBandDensity: 0.8,
    textureColorBlending: 0.06,
    textureSeed: 333,
    textureEase: 0.4,
    proceduralBackgroundColor: '#ffffff',
    textureShapeTriangles: 20,
    textureShapeCircles: 15,
    textureShapeBars: 15,
    textureShapeSquiggles: 10,
    domainWarpEnabled: false,
    domainWarpIntensity: 0,
    domainWarpScale: 3,
    vignetteIntensity: 0,
    vignetteRadius: 0.8,
    fresnelEnabled: false,
    fresnelPower: 2,
    fresnelIntensity: 0.5,
    fresnelColor: '#FFFFFF',
    iridescenceEnabled: false,
    iridescenceIntensity: 0.5,
    iridescenceSpeed: 1,
    prismEdgeEnabled: false,
    prismEdgeIntensity: 0.5,
    prismEdgeThinness: 3,
    prismEdgeSpread: 1,
    prismEdgeSpeed: 0.5,
    prismEdgeRipple: 1,
    bloomIntensity: 0,
    bloomThreshold: 0.7,
    chromaticAberration: 0,
    shapeType: 'plane',
    shapeRotationX: 0,
    shapeRotationY: 0,
    shapeRotationZ: 0,
    shapeAutoRotateSpeedX: 0,
    shapeAutoRotateSpeedY: 0,
    sphereRadius: 15,
    torusRadius: 15,
    torusTube: 5,
    cylinderRadius: 10,
    cylinderHeight: 40,
    planeBend: 0,
    planeTwist: 0,
    silhouetteFade: 0.25,
    cylinderFade: 0.08,
    ribbonFade: 0.05,
    flatShading: true,
    cameraLock: true,
    cameraX: 0,
    cameraY: 0,
    cameraZ: 0,
    cameraRotationX: 0,
    cameraRotationY: 0,
    cameraRotationZ: 0,
    cameraZoom: 1,
    });

}


function destroyNeatBackground() {
    if (gradientInstance) {
        gradientInstance.destroy?.();
        gradientInstance = null;
    }

    const canvas = document.getElementById('neat-home-background');
    if (canvas) {
        delete canvas.dataset.neatInitialized;
    }
}

// wire:navigate remplace le DOM sans recharger la page : il faut réinitialiser
// à chaque arrivée sur l'accueil, et détruire l'instance avant de quitter la
// page pour ne pas laisser tourner une boucle de rendu sur un canvas retiré du DOM.
document.addEventListener('DOMContentLoaded', initNeatBackground);
document.addEventListener('livewire:navigated', initNeatBackground);
document.addEventListener('livewire:navigating', destroyNeatBackground);