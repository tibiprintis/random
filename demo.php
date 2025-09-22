<?php
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XR-9 Immersive Flight Demo</title>
    <style>
        :root {
            color-scheme: dark;
        }
        * {
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
        }
        body {
            margin: 0;
            font-family: "Eurostile", "Segoe UI", "Roboto", sans-serif;
            color: #dff9ff;
            background: radial-gradient(circle at 50% 12%, rgba(40, 10, 70, 0.7), rgba(1, 3, 15, 0.95));
            overflow: hidden;
            letter-spacing: 0.02em;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(180deg, rgba(14, 18, 40, 0.45) 0%, rgba(5, 8, 18, 0.9) 100%);
            pointer-events: none;
            mix-blend-mode: lighten;
            opacity: 0.7;
        }
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.03) 0px, rgba(255, 255, 255, 0.03) 1px, transparent 1px, transparent 3px);
            pointer-events: none;
            opacity: 0.25;
        }
        canvas {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            display: block;
            background: transparent;
        }
        .overlay {
            position: fixed;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(1.5rem, 4vw, 3.2rem);
            pointer-events: none;
        }
        .overlay__header {
            max-width: min(540px, 90vw);
        }
        .overlay__title {
            margin: 0;
            font-size: clamp(1.6rem, 3vw, 3.2rem);
            letter-spacing: 0.4em;
            text-transform: uppercase;
            color: #7ff7ff;
            text-shadow: 0 0 12px rgba(120, 255, 255, 0.85);
        }
        .overlay__subtitle {
            margin: 0.75rem 0 0;
            color: rgba(200, 245, 255, 0.88);
            line-height: 1.55;
            font-size: clamp(0.9rem, 2vw, 1.1rem);
        }
        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: clamp(0.8rem, 1.8vw, 1.6rem);
            margin-top: clamp(1.4rem, 3vw, 2.2rem);
            max-width: min(820px, 95vw);
        }
        .metric {
            backdrop-filter: blur(8px);
            background: linear-gradient(160deg, rgba(12, 50, 90, 0.35), rgba(4, 10, 25, 0.65));
            border: 1px solid rgba(120, 255, 255, 0.25);
            border-radius: 12px;
            padding: 0.9rem 1.1rem;
            min-height: 96px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .metric::after {
            content: "";
            position: absolute;
            inset: auto -30% -50% -30%;
            height: 55%;
            background: radial-gradient(circle at 50% 0%, rgba(120, 255, 255, 0.25), transparent 65%);
            opacity: 0.6;
        }
        .metric__label {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.24em;
            color: rgba(140, 230, 255, 0.88);
        }
        .metric__value {
            font-size: clamp(1.4rem, 3vw, 2.3rem);
            font-weight: 600;
            color: #f1feff;
            text-shadow: 0 0 12px rgba(110, 255, 255, 0.6);
        }
        .metric__unit {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            color: rgba(140, 220, 255, 0.7);
        }
        .status {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.75rem;
            margin-top: clamp(1.6rem, 4vw, 2.6rem);
            max-width: min(780px, 95vw);
        }
        .status__item {
            padding: 0.75rem 1rem;
            border-left: 3px solid rgba(120, 255, 255, 0.6);
            background: linear-gradient(90deg, rgba(8, 32, 52, 0.65), rgba(6, 16, 28, 0.1));
            font-size: clamp(0.8rem, 1.5vw, 0.95rem);
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: rgba(190, 245, 255, 0.86);
        }
        .status__item span {
            color: #7ff7ff;
        }
        .overlay__footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1.2rem;
            flex-wrap: wrap;
            margin-top: clamp(1.2rem, 3vw, 1.8rem);
        }
        .overlay__footer small {
            font-size: clamp(0.7rem, 1.5vw, 0.9rem);
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: rgba(150, 230, 255, 0.72);
        }
        .compatibility {
            position: fixed;
            inset: auto 2rem 2rem 2rem;
            max-width: 520px;
            padding: 1.25rem 1.5rem;
            border: 1px solid rgba(255, 170, 140, 0.45);
            border-radius: 12px;
            background: linear-gradient(160deg, rgba(40, 20, 20, 0.85), rgba(10, 6, 6, 0.92));
            color: rgba(255, 230, 220, 0.95);
            box-shadow: 0 0 18px rgba(255, 90, 60, 0.28);
            pointer-events: auto;
            display: none;
            z-index: 10;
        }
        .compatibility strong {
            display: block;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .compatibility p {
            margin: 0.5rem 0 0;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        body.no-canvas .compatibility {
            display: block;
        }
        noscript .compatibility {
            display: block;
        }
        .noscript {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: rgba(2, 4, 10, 0.92);
            color: rgba(255, 220, 210, 0.95);
            z-index: 20;
            text-align: center;
        }
        @media (max-width: 780px) {
            .overlay {
                padding: 1.25rem;
            }
            .overlay__title {
                letter-spacing: 0.28em;
            }
            .metrics {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }
            .status {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
            .overlay__footer {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<canvas id="flightDisplay"></canvas>
<div class="overlay" aria-hidden="true">
    <header class="overlay__header">
        <h1 class="overlay__title">XR-9 • Descent Corridor</h1>
        <p class="overlay__subtitle">Autonomous scout vessel XR-9 is threading the crystalline trenches of Thessa Prime. Procedural terrain, volumetric auroras and energy storms are rendered live to showcase the atmospheric dynamics of an alien megastructure world.</p>
        <div class="metrics">
            <div class="metric">
                <span class="metric__label">Velocity</span>
                <span class="metric__value" id="velocityReadout">0.00</span>
                <span class="metric__unit">km/s</span>
            </div>
            <div class="metric">
                <span class="metric__label">Altitude</span>
                <span class="metric__value" id="altitudeReadout">0</span>
                <span class="metric__unit">m</span>
            </div>
            <div class="metric">
                <span class="metric__label">Anomaly Index</span>
                <span class="metric__value" id="anomalyReadout">0.00</span>
                <span class="metric__unit">Ψ</span>
            </div>
            <div class="metric">
                <span class="metric__label">Flux Shields</span>
                <span class="metric__value" id="fluxReadout">0</span>
                <span class="metric__unit">%</span>
            </div>
        </div>
    </header>
    <footer class="overlay__footer">
        <div class="status">
            <div class="status__item">Nav Mesh: <span id="navReadout">Calibrating</span></div>
            <div class="status__item">Autopilot: <span id="statusReadout">Engaged</span></div>
            <div class="status__item">Ion Collector: <span id="energyReadout">0%</span></div>
        </div>
        <small>Telemetry uplink secured • Atmospheric resonance nominal</small>
    </footer>
</div>
<div id="compatibilityWarning" class="compatibility" role="alert" aria-live="assertive">
    <strong>Interactive playback unavailable</strong>
    <p>Your device could not initialise the immersive renderer. A static briefing remains accessible; try a modern desktop browser for the full experience.</p>
</div>
<noscript>
    <div class="compatibility noscript" role="alert">
        <strong>JavaScript required</strong>
        <p>This tech demonstration relies on JavaScript to synthesise the alien landscape. Enable scripting to witness the full flythrough.</p>
    </div>
</noscript>
<script>
(function () {
    'use strict';

    const doc = document;
    const canvas = doc.getElementById('flightDisplay');
    if (!canvas) {
        return;
    }

    let context = null;
    try {
        context = canvas.getContext('2d', { alpha: false, desynchronized: true });
    } catch (err) {
        context = canvas.getContext('2d');
    }
    const ctx = context;

    const compatibilityWarning = doc.getElementById('compatibilityWarning');
    if (!ctx) {
        doc.body.classList.add('no-canvas');
        if (compatibilityWarning) {
            compatibilityWarning.style.display = 'block';
        }
        return;
    }

    const prefersReducedMotion = typeof window.matchMedia === 'function' ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
    let reduceMotion = prefersReducedMotion ? prefersReducedMotion.matches : false;
    if (prefersReducedMotion) {
        const handler = function (event) {
            reduceMotion = !!event.matches;
        };
        if (typeof prefersReducedMotion.addEventListener === 'function') {
            prefersReducedMotion.addEventListener('change', handler);
        } else if (typeof prefersReducedMotion.addListener === 'function') {
            prefersReducedMotion.addListener(handler);
        }
    }

    const velocityReadout = doc.getElementById('velocityReadout');
    const altitudeReadout = doc.getElementById('altitudeReadout');
    const anomalyReadout = doc.getElementById('anomalyReadout');
    const fluxReadout = doc.getElementById('fluxReadout');
    const navReadout = doc.getElementById('navReadout');
    const statusReadout = doc.getElementById('statusReadout');
    const energyReadout = doc.getElementById('energyReadout');

    const now = (typeof performance !== 'undefined' && performance && typeof performance.now === 'function')
        ? performance.now.bind(performance)
        : Date.now;
    const RAF = typeof window.requestAnimationFrame === 'function'
        ? window.requestAnimationFrame.bind(window)
        : function (cb) { return window.setTimeout(function () { cb(now()); }, 1000 / 60); };
    const CAF = typeof window.cancelAnimationFrame === 'function'
        ? window.cancelAnimationFrame.bind(window)
        : function (handle) { window.clearTimeout(handle); };

    let viewWidth = 1;
    let viewHeight = 1;
    let centerX = 0;
    let horizonY = 0;
    let perspective = 1;
    let skyGradient = null;
    let groundGradient = null;
    let dpr = window.devicePixelRatio || 1;

    const state = {
        running: false,
        lastTime: now(),
        time: 0,
        distance: 0,
        speed: 320,
        cam: {
            x: 0,
            y: 48,
            z: 0,
            pitch: -0.22,
            yaw: 0,
            roll: 0
        },
        turbulence: 0,
        energyStorm: 0,
        anomalyPulse: 0,
        navTimer: 0,
        navIndex: 0,
        autopilotPhase: 0,
        fluxCharge: 96
    };

    const navMessages = [
        'Mapping crystalline spires',
        'Cavern sonar lattice',
        'Plasma ducts aligned',
        'Gravity lens stable',
        'Vector drift compensated',
        'Subsurface echo return'
    ];
    const autopilotStates = [
        'Engaged',
        'Vectoring',
        'Micro-adjust',
        'Turbulence dampening',
        'Energy flare compensation'
    ];

    const shockwaves = [];
    const structures = [];
    const orbitals = [];
    const drones = [];
    const streaks = [];
    const auroraBands = [];
    const nebulaClusters = [];
    const starLayers = [];

    const canSetLineDash = typeof ctx.setLineDash === 'function';

    function lerp(a, b, t) {
        return a + (b - a) * t;
    }

    function fade(t) {
        return t * t * (3 - 2 * t);
    }

    const baseNoise = (function () {
        const cache = new Map();
        const seed = 94717;
        return function (x, y) {
            const key = x + ',' + y;
            if (cache.has(key)) {
                return cache.get(key);
            }
            const n = Math.sin((x * 15731 + y * 789221 + seed * 31) * 0.0001) * 43758.5453123;
            const value = n - Math.floor(n);
            cache.set(key, value);
            return value;
        };
    }());

    function smoothNoise(x, y) {
        const x0 = Math.floor(x);
        const y0 = Math.floor(y);
        const xf = x - x0;
        const yf = y - y0;

        const v1 = baseNoise(x0, y0);
        const v2 = baseNoise(x0 + 1, y0);
        const v3 = baseNoise(x0, y0 + 1);
        const v4 = baseNoise(x0 + 1, y0 + 1);

        const i1 = lerp(v1, v2, fade(xf));
        const i2 = lerp(v3, v4, fade(xf));
        return lerp(i1, i2, fade(yf));
    }

    function fbm(x, y) {
        let total = 0;
        let amplitude = 1;
        let frequency = 0.0075;
        for (let i = 0; i < 5; i += 1) {
            total += smoothNoise(x * frequency, y * frequency) * amplitude;
            amplitude *= 0.5;
            frequency *= 2;
        }
        return total;
    }

    function resize() {
        dpr = window.devicePixelRatio || 1;
        viewWidth = Math.max(1, window.innerWidth);
        viewHeight = Math.max(1, window.innerHeight);
        canvas.width = viewWidth * dpr;
        canvas.height = viewHeight * dpr;
        canvas.style.width = viewWidth + 'px';
        canvas.style.height = viewHeight + 'px';
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(dpr, dpr);

        centerX = viewWidth / 2;
        horizonY = viewHeight * 0.46;
        perspective = viewWidth * 1.12;

        skyGradient = ctx.createLinearGradient(0, 0, 0, viewHeight);
        skyGradient.addColorStop(0, '#040414');
        skyGradient.addColorStop(0.3, '#071337');
        skyGradient.addColorStop(0.7, '#10062d');
        skyGradient.addColorStop(1, '#040216');

        groundGradient = ctx.createLinearGradient(0, horizonY, 0, viewHeight);
        groundGradient.addColorStop(0, 'rgba(20, 32, 60, 0)');
        groundGradient.addColorStop(0.3, 'rgba(14, 40, 58, 0.25)');
        groundGradient.addColorStop(0.7, 'rgba(6, 14, 28, 0.75)');
        groundGradient.addColorStop(1, 'rgba(2, 6, 14, 1)');
    }

    function initialise() {
        for (let i = 0; i < 3; i += 1) {
            auroraBands.push({
                resolution: 36,
                speed: 0.45 + Math.random() * 0.35,
                phase: Math.random() * Math.PI * 2,
                color: [80 + i * 20, 200 + i * 18, 255],
                intensity: 0.6 + Math.random() * 0.35,
                elevation: i * 28
            });
        }

        for (let i = 0; i < 5; i += 1) {
            nebulaClusters.push({
                anchorX: (Math.random() - 0.5) * 2200,
                anchorY: -260 - Math.random() * 160,
                depth: 600 + Math.random() * 1200,
                radius: 320 + Math.random() * 220,
                hue: 190 + Math.random() * 40,
                intensity: 0.4 + Math.random() * 0.4,
                drift: 0.2 + Math.random() * 0.4,
                phase: Math.random() * Math.PI * 2
            });
        }

        const starConfigurations = [
            { count: 280, depth: 2400, speed: 0.35 },
            { count: 220, depth: 3200, speed: 0.2 },
            { count: 180, depth: 4200, speed: 0.12 }
        ];
        starConfigurations.forEach(function (config) {
            const layer = {
                depth: config.depth,
                speedMultiplier: config.speed,
                stars: []
            };
            for (let i = 0; i < config.count; i += 1) {
                layer.stars.push({
                    x: (Math.random() - 0.5) * 2400,
                    y: -Math.random() * 520 - 60,
                    z: Math.random() * config.depth + 200,
                    baseBrightness: 0.25 + Math.random() * 0.75,
                    twinkleSpeed: 0.4 + Math.random() * 1.4,
                    colorShift: Math.random() * 0.6,
                    layerDepth: config.depth
                });
            }
            starLayers.push(layer);
        });

        for (let i = 0; i < 120; i += 1) {
            structures.push(createStructure(240 + i * 48));
        }
        for (let i = 0; i < 28; i += 1) {
            orbitals.push(createOrbital(400 + i * 120));
        }
        for (let i = 0; i < 10; i += 1) {
            drones.push(createDrone(600 + i * 180));
        }
        for (let i = 0; i < 200; i += 1) {
            streaks.push(createStreak(200 + i * 18));
        }
    }

    function createStructure(offset) {
        return {
            z: state.distance + offset,
            x: (Math.random() - 0.5) * 560,
            baseWidth: 10 + Math.random() * 30,
            height: 80 + Math.random() * 320,
            tiers: 2 + Math.floor(Math.random() * 4),
            glow: 0.25 + Math.random() * 0.7,
            seed: Math.random() * Math.PI * 2,
            banding: Math.random(),
            tilt: (Math.random() - 0.5) * 0.2
        };
    }

    function recycleStructure(structure) {
        structure.z = state.distance + 900 + Math.random() * 1800;
        structure.x = (Math.random() - 0.5) * 580;
        structure.baseWidth = 10 + Math.random() * 32;
        structure.height = 90 + Math.random() * 360;
        structure.tiers = 2 + Math.floor(Math.random() * 5);
        structure.glow = 0.3 + Math.random() * 0.6;
        structure.seed = Math.random() * Math.PI * 2;
        structure.banding = Math.random();
        structure.tilt = (Math.random() - 0.5) * 0.22;
    }

    function createOrbital(offset) {
        return {
            z: state.distance + offset,
            baseX: (Math.random() - 0.5) * 520,
            baseY: -120 - Math.random() * 140,
            amplitude: 18 + Math.random() * 26,
            frequency: 0.8 + Math.random() * 0.6,
            radius: 18 + Math.random() * 22,
            glow: 0.4 + Math.random() * 0.5,
            phase: Math.random() * Math.PI * 2
        };
    }

    function recycleOrbital(orbital) {
        orbital.z = state.distance + 1200 + Math.random() * 1400;
        orbital.baseX = (Math.random() - 0.5) * 520;
        orbital.baseY = -120 - Math.random() * 160;
        orbital.amplitude = 18 + Math.random() * 26;
        orbital.frequency = 0.6 + Math.random() * 0.8;
        orbital.radius = 18 + Math.random() * 24;
        orbital.glow = 0.4 + Math.random() * 0.5;
        orbital.phase = Math.random() * Math.PI * 2;
    }

    function createDrone(offset) {
        return {
            z: state.distance + offset,
            baseX: (Math.random() - 0.5) * 380,
            baseY: -80 - Math.random() * 160,
            radius: 14 + Math.random() * 16,
            frequency: 0.6 + Math.random() * 0.6,
            phase: Math.random() * Math.PI * 2,
            ring: 1 + Math.random() * 1.4
        };
    }

    function recycleDrone(drone) {
        drone.z = state.distance + 1000 + Math.random() * 1600;
        drone.baseX = (Math.random() - 0.5) * 420;
        drone.baseY = -80 - Math.random() * 160;
        drone.radius = 14 + Math.random() * 18;
        drone.frequency = 0.6 + Math.random() * 0.6;
        drone.phase = Math.random() * Math.PI * 2;
        drone.ring = 1 + Math.random() * 1.6;
    }

    function createStreak(offset) {
        return {
            z: state.distance + offset,
            baseX: (Math.random() - 0.5) * 360,
            baseY: -20 - Math.random() * 40,
            length: 60 + Math.random() * 160,
            hue: Math.random(),
            wobble: Math.random() * 0.6 + 0.2
        };
    }

    function recycleStreak(streak) {
        streak.z = state.distance + 800 + Math.random() * 2000;
        streak.baseX = (Math.random() - 0.5) * 380;
        streak.baseY = -20 - Math.random() * 40;
        streak.length = 60 + Math.random() * 160;
        streak.hue = Math.random();
        streak.wobble = Math.random() * 0.6 + 0.2;
    }

    function project(x, y, z) {
        const dx = x - state.cam.x;
        const dy = y - state.cam.y;
        const dz = z - state.cam.z;
        if (dz <= 1) {
            return null;
        }
        const cosYaw = Math.cos(state.cam.yaw);
        const sinYaw = Math.sin(state.cam.yaw);
        const cosPitch = Math.cos(state.cam.pitch);
        const sinPitch = Math.sin(state.cam.pitch);
        const cosRoll = Math.cos(state.cam.roll);
        const sinRoll = Math.sin(state.cam.roll);

        const yawX = dx * cosYaw - dz * sinYaw;
        const yawZ = dz * cosYaw + dx * sinYaw;
        const pitchY = dy * cosPitch - yawZ * sinPitch;
        const pitchZ = yawZ * cosPitch + dy * sinPitch;
        const rollX = yawX * cosRoll - pitchY * sinRoll;
        const rollY = pitchY * cosRoll + yawX * sinRoll;

        const depth = pitchZ;
        if (depth <= 1) {
            return null;
        }
        const scale = perspective / depth;
        const screenX = centerX + rollX * scale;
        const screenY = horizonY + rollY * scale;

        if (screenX < -viewWidth * 0.5 || screenX > viewWidth * 1.5 || screenY < -viewHeight * 0.5 || screenY > viewHeight * 1.5) {
            return null;
        }
        return { x: screenX, y: screenY, scale: scale, depth: depth };
    }

    function updateState(delta) {
        const effectiveDelta = reduceMotion ? delta * 0.6 : delta;
        state.time += effectiveDelta;

        const targetSpeed = 330 + Math.sin(state.time * 0.25) * 90 + state.energyStorm * 220;
        state.speed += (targetSpeed - state.speed) * Math.min(1, effectiveDelta * 0.8);
        state.distance += state.speed * effectiveDelta;
        state.cam.z = state.distance;

        const sway = Math.sin(state.distance * 0.0012) * 60;
        const lateralDrift = Math.sin(state.time * 0.8) * state.turbulence * 24;
        state.cam.x = sway + lateralDrift;
        state.cam.y = 48 + Math.sin(state.distance * 0.0007) * 14 + state.turbulence * 22;
        state.cam.pitch = -0.22 + Math.sin(state.distance * 0.0005) * 0.05 - state.turbulence * 0.03;
        state.cam.roll = Math.sin(state.distance * 0.0009) * 0.25 + state.turbulence * 0.28;
        state.cam.yaw = Math.sin(state.distance * 0.0008) * 0.08;

        state.turbulence = Math.max(0, state.turbulence - effectiveDelta * 0.45);
        state.energyStorm = Math.max(0, state.energyStorm - effectiveDelta * 0.3);
        state.anomalyPulse = Math.max(0, state.anomalyPulse - effectiveDelta * 0.4);

        if (!reduceMotion && Math.random() < effectiveDelta * 0.5) {
            state.turbulence = Math.min(1.2, state.turbulence + 0.7 * Math.random());
            state.anomalyPulse = 1;
            spawnShockwave();
        }
        if (Math.random() < effectiveDelta * 0.25) {
            state.energyStorm = Math.min(1.4, state.energyStorm + 0.8 * Math.random());
        }

        state.navTimer += effectiveDelta;
        if (state.navTimer > 3.8) {
            state.navTimer = 0;
            state.navIndex = (state.navIndex + 1) % navMessages.length;
            if (navReadout) {
                navReadout.textContent = navMessages[state.navIndex];
            }
        }

        state.autopilotPhase += effectiveDelta * 0.35;
        const autopilotIndex = Math.floor(state.autopilotPhase) % autopilotStates.length;
        if (statusReadout) {
            statusReadout.textContent = autopilotStates[autopilotIndex];
        }

        const velocity = state.speed / 100;
        const altitude = 4200 + Math.sin(state.distance * 0.0011) * 1200 + state.turbulence * 320;
        const anomaly = 1.6 + Math.sin(state.time * 0.8) * 0.4 + state.anomalyPulse * 1.4;
        state.fluxCharge += ((92 + Math.sin(state.time * 0.45) * 8 + state.energyStorm * 6) - state.fluxCharge) * Math.min(1, effectiveDelta * 2);
        const ionCollector = 46 + Math.sin(state.time * 0.3) * 22 + state.energyStorm * 42;

        if (velocityReadout) {
            velocityReadout.textContent = velocity.toFixed(2);
        }
        if (altitudeReadout) {
            altitudeReadout.textContent = altitude.toFixed(0);
        }
        if (anomalyReadout) {
            anomalyReadout.textContent = anomaly.toFixed(2);
        }
        if (fluxReadout) {
            fluxReadout.textContent = state.fluxCharge.toFixed(0);
        }
        if (energyReadout) {
            energyReadout.textContent = ionCollector.toFixed(0) + '%';
        }
    }

    function spawnShockwave() {
        shockwaves.push({ time: 0 });
    }

    function clearScene() {
        ctx.fillStyle = skyGradient || '#030614';
        ctx.fillRect(0, 0, viewWidth, viewHeight);
    }

    function drawNebula(delta) {
        ctx.save();
        ctx.globalCompositeOperation = 'lighter';
        for (let i = 0; i < nebulaClusters.length; i += 1) {
            const cluster = nebulaClusters[i];
            cluster.phase += delta * cluster.drift;
            const wobble = Math.sin(state.time * 0.2 + cluster.phase) * 120;
            const x = cluster.anchorX + wobble;
            const y = cluster.anchorY + Math.cos(state.time * 0.15 + cluster.phase) * 40;
            const position = project(x, y, state.cam.z + cluster.depth);
            if (!position) {
                continue;
            }
            const radius = Math.max(80, cluster.radius * position.scale * 0.6);
            const gradient = ctx.createRadialGradient(position.x, position.y, 0, position.x, position.y, radius);
            gradient.addColorStop(0, 'rgba(255, 255, 255, 0.35)');
            gradient.addColorStop(0.4, 'hsla(' + cluster.hue + ', 80%, 70%, ' + (0.35 + cluster.intensity * 0.35) + ')');
            gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
            ctx.globalAlpha = 0.7;
            ctx.beginPath();
            ctx.arc(position.x, position.y, radius, 0, Math.PI * 2);
            ctx.fillStyle = gradient;
            ctx.fill();
        }
        ctx.restore();
    }

    function drawStars(delta) {
        ctx.save();
        ctx.globalCompositeOperation = 'lighter';
        for (let l = 0; l < starLayers.length; l += 1) {
            const layer = starLayers[l];
            const speedFactor = layer.speedMultiplier * (reduceMotion ? 0.4 : 1);
            for (let i = 0; i < layer.stars.length; i += 1) {
                const star = layer.stars[i];
                star.z -= state.speed * delta * speedFactor;
                if (star.z < 200) {
                    star.z += layer.depth;
                    star.x = (Math.random() - 0.5) * 2400;
                    star.y = -Math.random() * 520 - 60;
                    star.baseBrightness = 0.25 + Math.random() * 0.75;
                    star.twinkleSpeed = 0.4 + Math.random() * 1.4;
                }
                const offsetX = star.x + Math.sin(state.distance * 0.00025 + star.colorShift * 4) * 60;
                const position = project(offsetX, star.y, state.cam.z + star.z);
                if (!position) {
                    continue;
                }
                const size = Math.max(1.3, 4.2 * position.scale);
                const twinkle = star.baseBrightness + Math.sin(state.time * star.twinkleSpeed + star.colorShift * 6) * 0.35 + state.energyStorm * 0.2;
                const alpha = Math.min(1, 0.3 + twinkle * 0.6);
                ctx.fillStyle = 'rgba(' + (180 + star.colorShift * 60).toFixed(0) + ', ' + (220 + twinkle * 40).toFixed(0) + ', 255, ' + alpha + ')';
                ctx.beginPath();
                ctx.arc(position.x, position.y, size, 0, Math.PI * 2);
                ctx.fill();
            }
        }
        ctx.restore();
    }

    function drawAurora(delta) {
        ctx.save();
        ctx.globalCompositeOperation = 'screen';
        for (let i = 0; i < auroraBands.length; i += 1) {
            const band = auroraBands[i];
            band.phase += delta * band.speed;
            ctx.beginPath();
            for (let p = 0; p <= band.resolution; p += 1) {
                const t = p / band.resolution;
                const x = centerX + (t - 0.5) * viewWidth * 1.4;
                const amplitude = 80 + state.energyStorm * 80;
                const y = horizonY - 220 - band.elevation + Math.sin(t * Math.PI * 2 + band.phase) * amplitude * band.intensity;
                if (p === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            }
            const gradient = ctx.createLinearGradient(centerX - viewWidth * 0.7, horizonY - 320, centerX + viewWidth * 0.7, horizonY - 260);
            gradient.addColorStop(0, 'rgba(' + band.color[0] + ', ' + band.color[1] + ', ' + band.color[2] + ', 0)');
            gradient.addColorStop(0.5, 'rgba(' + band.color[0] + ', ' + band.color[1] + ', ' + band.color[2] + ', ' + (0.22 + band.intensity * 0.25 + state.energyStorm * 0.25) + ')');
            gradient.addColorStop(1, 'rgba(' + band.color[0] + ', ' + band.color[1] + ', ' + band.color[2] + ', 0)');
            ctx.strokeStyle = gradient;
            ctx.lineWidth = 2.5 + band.intensity * 2.6;
            ctx.stroke();
        }
        ctx.restore();
    }

    function drawHorizonGlow() {
        ctx.save();
        ctx.globalCompositeOperation = 'screen';
        const pulse = 0.5 + Math.sin(state.time * 1.2) * 0.1 + state.energyStorm * 0.45;
        const gradient = ctx.createRadialGradient(centerX, horizonY + 40, 12, centerX, horizonY + 40, viewWidth * 0.8);
        gradient.addColorStop(0, 'rgba(180, 255, 255, ' + (0.25 + pulse * 0.4) + ')');
        gradient.addColorStop(0.45, 'rgba(80, 160, 255, ' + (0.18 + pulse * 0.3) + ')');
        gradient.addColorStop(1, 'rgba(10, 20, 50, 0)');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, viewWidth, viewHeight);
        ctx.restore();
    }

    function drawTerrain() {
        const segmentLength = 36;
        const totalSegments = 120;
        const baseWidth = 360;
        ctx.save();
        ctx.translate(centerX, horizonY);
        let previousLeft = null;
        let previousRight = null;
        for (let i = 1; i < totalSegments; i += 1) {
            const depth = i * segmentLength;
            const worldZ = state.distance + depth;
            const scale = perspective / (perspective + depth);
            const ridge = fbm(worldZ * 0.012, state.cam.x * 0.008) - 0.5;
            const noiseOffset = fbm(worldZ * 0.018 + 5000, state.cam.x * 0.009) - 0.5;
            const y = (noiseOffset * 120 - depth * 0.032 - 18) * scale;
            const width = (baseWidth + ridge * 220) * scale;
            const leftX = -width;
            const rightX = width;

            if (previousLeft) {
                ctx.beginPath();
                ctx.moveTo(previousLeft.x, previousLeft.y);
                ctx.lineTo(previousRight.x, previousRight.y);
                ctx.lineTo(rightX, y);
                ctx.lineTo(leftX, y);
                ctx.closePath();
                const gradient = ctx.createLinearGradient(0, previousLeft.y, 0, y + 120 * scale);
                gradient.addColorStop(0, 'rgba(10, 75, 120, ' + (0.35 + ridge * 0.4 + state.energyStorm * 0.25) + ')');
                gradient.addColorStop(0.65, 'rgba(12, 24, 48, 0.82)');
                gradient.addColorStop(1, 'rgba(6, 12, 24, 0.95)');
                ctx.fillStyle = gradient;
                ctx.fill();

                ctx.strokeStyle = 'rgba(120, 255, 255, ' + (0.12 + state.energyStorm * 0.25) + ')';
                ctx.lineWidth = Math.max(0.6, 2 * scale);
                ctx.beginPath();
                ctx.moveTo(leftX, y);
                ctx.lineTo(rightX, y);
                ctx.stroke();
            }

            previousLeft = { x: leftX, y: y };
            previousRight = { x: rightX, y: y };
        }
        ctx.restore();

        ctx.save();
        ctx.globalAlpha = 0.6;
        ctx.fillStyle = groundGradient || 'rgba(10, 16, 28, 0.85)';
        ctx.fillRect(0, horizonY, viewWidth, viewHeight - horizonY);
        ctx.restore();
    }

    function drawEnergyRivers() {
        ctx.save();
        ctx.translate(centerX, horizonY + 4);
        ctx.globalCompositeOperation = 'screen';
        const segmentLength = 42;
        const totalSegments = 90;
        let previousPoint = null;
        for (let i = 0; i < totalSegments; i += 1) {
            const depth = i * segmentLength;
            const worldZ = state.distance + depth;
            const scale = perspective / (perspective + depth);
            const noiseOffset = fbm(worldZ * 0.02 + 800, state.cam.x * 0.006) - 0.5;
            const x = (noiseOffset * 220 + Math.sin(worldZ * 0.0024) * 40) * scale;
            const y = (Math.cos(worldZ * 0.0028) * 26 - depth * 0.014) * scale;
            if (previousPoint) {
                ctx.strokeStyle = 'rgba(120, 220, 255, ' + (0.28 + state.energyStorm * 0.35) + ')';
                ctx.lineWidth = 3.2 * scale + 0.4;
                ctx.beginPath();
                ctx.moveTo(previousPoint.x, previousPoint.y);
                ctx.lineTo(x, y);
                ctx.stroke();

                ctx.strokeStyle = 'rgba(40, 140, 255, ' + (0.55 + state.energyStorm * 0.3) + ')';
                ctx.lineWidth = 1.4 * scale + 0.2;
                ctx.beginPath();
                ctx.moveTo(previousPoint.x, previousPoint.y);
                ctx.lineTo(x, y);
                ctx.stroke();
            }
            previousPoint = { x: x, y: y };
        }
        ctx.restore();
    }

    function drawStructures(delta) {
        ctx.save();
        ctx.globalCompositeOperation = 'lighter';
        for (let i = 0; i < structures.length; i += 1) {
            const structure = structures[i];
            if (structure.z < state.cam.z - 120) {
                recycleStructure(structure);
            }
            const wobble = Math.sin(state.time * 0.6 + structure.seed) * structure.tilt * 60;
            const baseY = -24 - structure.height * 0.5;
            const topPoint = project(structure.x + wobble, baseY - structure.height, structure.z);
            const basePoint = project(structure.x, baseY, structure.z);
            if (!topPoint || !basePoint) {
                continue;
            }
            const width = Math.max(1.2, structure.baseWidth * basePoint.scale * 0.6);
            ctx.strokeStyle = 'rgba(' + (80 + structure.glow * 120).toFixed(0) + ', ' + (200 + structure.glow * 40).toFixed(0) + ', 255, ' + (0.5 + structure.glow * 0.4 + state.energyStorm * 0.2) + ')';
            ctx.lineWidth = width;
            ctx.beginPath();
            ctx.moveTo(topPoint.x, topPoint.y);
            ctx.lineTo(basePoint.x, basePoint.y);
            ctx.stroke();

            ctx.globalAlpha = 0.3 + structure.glow * 0.35 + state.energyStorm * 0.25;
            ctx.beginPath();
            ctx.arc(topPoint.x, topPoint.y, width * 2.6, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(120, 255, 255, 0.45)';
            ctx.fill();
            ctx.globalAlpha = 1;

            ctx.strokeStyle = 'rgba(20, 140, 255, ' + (0.45 + structure.glow * 0.2) + ')';
            ctx.lineWidth = Math.max(0.6, width * 0.7);
            for (let t = 0; t < structure.tiers; t += 1) {
                const tierHeight = baseY - t * (structure.height / structure.tiers);
                const tierPoint = project(structure.x, tierHeight, structure.z);
                if (!tierPoint) {
                    continue;
                }
                const tierWidth = width * (1 + t * 0.15);
                ctx.beginPath();
                ctx.ellipse(tierPoint.x, tierPoint.y, tierWidth * 1.2, tierWidth * 0.8, wobble * 0.015, 0, Math.PI * 2);
                ctx.stroke();
            }
        }
        ctx.restore();
    }

    function drawOrbitals(delta) {
        ctx.save();
        ctx.globalCompositeOperation = 'lighter';
        for (let i = 0; i < orbitals.length; i += 1) {
            const orbital = orbitals[i];
            if (orbital.z < state.cam.z - 80) {
                recycleOrbital(orbital);
            }
            const x = orbital.baseX + Math.sin(state.time * orbital.frequency + orbital.phase) * orbital.amplitude;
            const y = orbital.baseY + Math.cos(state.time * (orbital.frequency * 0.7) + orbital.phase) * (orbital.amplitude * 0.6);
            const position = project(x, y, orbital.z);
            if (!position) {
                continue;
            }
            const radius = Math.max(6, orbital.radius * position.scale * 1.2);
            const gradient = ctx.createRadialGradient(position.x, position.y, 0, position.x, position.y, radius);
            gradient.addColorStop(0, 'rgba(255, 255, 255, 0.8)');
            gradient.addColorStop(0.5, 'rgba(100, 200, 255, ' + (0.35 + orbital.glow * 0.4) + ')');
            gradient.addColorStop(1, 'rgba(10, 40, 80, 0)');
            ctx.beginPath();
            ctx.arc(position.x, position.y, radius, 0, Math.PI * 2);
            ctx.fillStyle = gradient;
            ctx.fill();

            ctx.strokeStyle = 'rgba(80, 200, 255, 0.35)';
            ctx.lineWidth = 0.8 + radius * 0.12;
            ctx.beginPath();
            ctx.arc(position.x, position.y, radius * 1.4, 0, Math.PI * 2);
            ctx.stroke();
        }
        ctx.restore();
    }

    function drawDrones() {
        ctx.save();
        ctx.globalCompositeOperation = 'lighter';
        for (let i = 0; i < drones.length; i += 1) {
            const drone = drones[i];
            if (drone.z < state.cam.z - 60) {
                recycleDrone(drone);
            }
            const x = drone.baseX + Math.sin(state.time * drone.frequency + drone.phase) * 30;
            const y = drone.baseY + Math.cos(state.time * (drone.frequency * 0.8) + drone.phase) * 26;
            const position = project(x, y, drone.z);
            if (!position) {
                continue;
            }
            const radius = Math.max(4, drone.radius * position.scale * 1.4);
            ctx.fillStyle = 'rgba(255, 255, 255, 0.65)';
            ctx.beginPath();
            ctx.arc(position.x, position.y, radius, 0, Math.PI * 2);
            ctx.fill();

            if (canSetLineDash) {
                ctx.setLineDash([radius * 0.6, radius * 0.4]);
            }
            ctx.strokeStyle = 'rgba(120, 255, 255, ' + (0.25 + state.anomalyPulse * 0.35) + ')';
            ctx.lineWidth = 1 + radius * 0.25;
            ctx.beginPath();
            ctx.arc(position.x, position.y, radius * (1.6 + Math.sin(state.time + drone.phase) * 0.35), 0, Math.PI * 2);
            ctx.stroke();
            if (canSetLineDash) {
                ctx.setLineDash([]);
            }
        }
        ctx.restore();
    }

    function drawStreaks() {
        ctx.save();
        ctx.globalCompositeOperation = 'screen';
        for (let i = 0; i < streaks.length; i += 1) {
            const streak = streaks[i];
            if (streak.z - state.cam.z < 60) {
                recycleStreak(streak);
            }
            const base = project(
                streak.baseX + Math.sin(state.time * 1.1 + streak.hue * 6) * 18,
                streak.baseY,
                streak.z
            );
            const tip = project(
                streak.baseX,
                streak.baseY - streak.length,
                streak.z - 80
            );
            if (!base || !tip) {
                continue;
            }
            ctx.strokeStyle = 'rgba(120, 220, 255, ' + (0.22 + state.energyStorm * 0.3) + ')';
            ctx.lineWidth = Math.max(0.8, base.scale * 6);
            ctx.beginPath();
            ctx.moveTo(base.x, base.y);
            ctx.lineTo(tip.x, tip.y);
            ctx.stroke();
        }
        ctx.restore();
    }

    function drawShockwaves(delta) {
        ctx.save();
        ctx.globalCompositeOperation = 'screen';
        for (let i = shockwaves.length - 1; i >= 0; i -= 1) {
            const wave = shockwaves[i];
            wave.time += delta * 1.2;
            const progress = wave.time;
            const radius = progress * viewWidth * 0.65;
            const alpha = Math.max(0, 0.45 - progress * 0.4);
            if (alpha <= 0) {
                shockwaves.splice(i, 1);
                continue;
            }
            ctx.strokeStyle = 'rgba(120, 255, 255, ' + alpha + ')';
            ctx.lineWidth = 3 + progress * 16;
            ctx.beginPath();
            ctx.arc(centerX, horizonY + Math.sin(state.time) * 18, radius, 0, Math.PI * 2);
            ctx.stroke();
        }
        ctx.restore();
    }

    function drawTargeting() {
        ctx.save();
        ctx.globalCompositeOperation = 'screen';
        const radius = 60 + Math.sin(state.time * 2.1) * 8 + state.turbulence * 30;
        ctx.strokeStyle = 'rgba(120, 255, 255, 0.35)';
        ctx.lineWidth = 1.2;
        ctx.beginPath();
        ctx.arc(centerX, horizonY + 60, radius, 0, Math.PI * 2);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(centerX - radius - 12, horizonY + 60);
        ctx.lineTo(centerX - radius + 6, horizonY + 60);
        ctx.moveTo(centerX + radius - 6, horizonY + 60);
        ctx.lineTo(centerX + radius + 12, horizonY + 60);
        ctx.moveTo(centerX, horizonY + 60 - radius - 12);
        ctx.lineTo(centerX, horizonY + 60 - radius + 6);
        ctx.moveTo(centerX, horizonY + 60 + radius - 6);
        ctx.lineTo(centerX, horizonY + 60 + radius + 12);
        ctx.stroke();
        ctx.restore();
    }

    function drawDistortion() {
        ctx.save();
        ctx.globalCompositeOperation = 'overlay';
        ctx.globalAlpha = 0.07 + state.turbulence * 0.12;
        for (let y = 0; y < viewHeight; y += 3) {
            ctx.fillStyle = 'rgba(10, 20, 40, ' + (0.35 + Math.sin(state.time * 50 + y * 0.1) * 0.05) + ')';
            ctx.fillRect(0, y, viewWidth, 1);
        }
        ctx.restore();

        ctx.save();
        ctx.globalAlpha = 0.1 + state.energyStorm * 0.2;
        const glitchHeight = 1 + Math.sin(state.time * 20) * 2;
        for (let i = 0; i < 6; i += 1) {
            const y = ((state.time * 140 + i * 200) % viewHeight) | 0;
            ctx.fillStyle = 'rgba(60, 160, 255, 0.12)';
            ctx.fillRect(0, y, viewWidth, glitchHeight);
        }
        ctx.restore();
    }

    function render(delta) {
        clearScene();
        drawNebula(delta);
        drawStars(delta);
        drawAurora(delta);
        drawHorizonGlow();
        drawTerrain();
        drawEnergyRivers();
        drawStructures(delta);
        drawOrbitals(delta);
        drawDrones();
        drawStreaks();
        drawShockwaves(delta);
        drawTargeting();
        drawDistortion();
    }

    let rafHandle = null;

    function step(timestamp) {
        if (!state.running) {
            return;
        }
        const current = typeof timestamp === 'number' ? timestamp : now();
        const delta = Math.min(0.05, (current - state.lastTime) / 1000 || 0.016);
        state.lastTime = current;
        try {
            updateState(delta);
            render(delta);
        } catch (error) {
            console.error('Renderer error', error);
            stop();
            doc.body.classList.add('no-canvas');
            if (compatibilityWarning) {
                compatibilityWarning.style.display = 'block';
                compatibilityWarning.querySelector('p').textContent = 'An unexpected error interrupted the renderer. Reload to retry the flythrough.';
            }
            return;
        }
        rafHandle = RAF(step);
    }

    function start() {
        if (state.running) {
            return;
        }
        state.running = true;
        state.lastTime = now();
        rafHandle = RAF(step);
    }

    function stop() {
        if (!state.running) {
            return;
        }
        state.running = false;
        if (rafHandle !== null) {
            CAF(rafHandle);
            rafHandle = null;
        }
    }

    window.addEventListener('resize', function () {
        resize();
    }, { passive: true });

    if (typeof document !== 'undefined' && typeof document.addEventListener === 'function') {
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stop();
            } else {
                start();
            }
        });
    }

    initialise();
    resize();
    start();
}());
</script>
</body>
</html>
