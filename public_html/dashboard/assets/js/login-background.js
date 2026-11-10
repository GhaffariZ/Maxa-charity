(function () {
  'use strict';
  const background = document.getElementById('loginBackground');
  if (!background || !window.Wayglim) return;
  const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
  if (reducedMotion.matches) return;
  let motion;
  const showStill = () => { motion?.destroy(); motion = null; background.replaceChildren(); };
  try {
    motion = window.Wayglim.mount(background, {
      aspectRatio: 0,
      background: [0.024, 0.106, 0.114],
      durationSeconds: 60,
      startSeconds: 18,
      pace: 0.28,
      maxPixelRatio: 0.5,
      onSlow: showStill,
      pauseOffscreen: true,
      respectReducedMotion: true,
      // Grain is disabled; its unused texture stays neutral.
      noise: btoa('\x80'.repeat(128 * 128)),
      params: {
        balanceX: 0, balanceY: 0, bend: 62, chromaSplit: 8,
        cornerRadius: 0, curve: 'line', curveDepth: 0,
        customColors: [[0, 0.56, 0.54], [0.20, 0.78, 0.71], [0.98, 0.65, 0.10], [0, 0.39, 0.38]],
        effectArea: 'core', form: 'flow', formRotate: 0, formRound: 35, formSize: 50, formSpin: 0,
        frameShape: 'rect', glow: 40, height: 56, inset: 0,
        grainEnabled: false, grainAmount: 0, grainDistortion: 0, grainDrift: 0,
        grainScale: 70, grainSoftness: 50, surfaceGrain: true,
        lens: { enabled: false }, masks: [], masksEnabled: false,
        lightTravel: 50, mode: 'diagonal', phase: 30,
        pointer: { enabled: false }, position: 50, refraction: 49,
        saturation: 86, secondaryMotion: 38, shading: 0, softness: 70,
        spectrum: 'custom', speed: 50, spread: 62, wobble: 42
      }
    });
    reducedMotion.addEventListener?.('change', event => { if (event.matches) showStill(); });
  } catch (_) {
    background.replaceChildren();
  }
})();
