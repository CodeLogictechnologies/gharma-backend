<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0a0f;
            overflow: hidden;
            position: relative;
            color: #fff;
        }

        /* ── Stars ── */
        .stars {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .star {
            position: absolute;
            border-radius: 50%;
            background: #fff;
            animation: twinkle var(--d) ease-in-out infinite alternate;
        }

        @keyframes twinkle {
            0% {
                opacity: .1;
                transform: scale(1);
            }

            100% {
                opacity: .9;
                transform: scale(1.4);
            }
        }

        /* ── Scan line ── */
        .scan-line {
            position: fixed;
            top: -40px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(127, 119, 221, .5), transparent);
            animation: scan 4s ease-in-out infinite;
            pointer-events: none;
            z-index: 2;
        }

        @keyframes scan {
            0% {
                top: -40px;
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                top: 110%;
                opacity: 0;
            }
        }

        /* ── Main scene ── */
        .scene {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem;
            max-width: 520px;
            width: 100%;
        }

        /* ── 404 Glitch ── */
        .num {
            font-size: clamp(100px, 22vw, 180px);
            font-weight: 700;
            line-height: 1;
            color: #fff;
            letter-spacing: -4px;
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .num::before,
        .num::after {
            content: attr(data-text);
            position: absolute;
            inset: 0;
            font-size: inherit;
            font-weight: inherit;
            letter-spacing: inherit;
            line-height: inherit;
        }

        .num::before {
            color: #7F77DD;
            animation: glitch1 3s infinite;
            clip-path: polygon(0 20%, 100% 20%, 100% 40%, 0 40%);
        }

        .num::after {
            color: #1D9E75;
            animation: glitch2 3s infinite;
            clip-path: polygon(0 60%, 100% 60%, 100% 80%, 0 80%);
        }

        @keyframes glitch1 {

            0%,
            90%,
            100% {
                transform: translateX(0);
            }

            91% {
                transform: translateX(-4px);
            }

            93% {
                transform: translateX(4px);
            }

            95% {
                transform: translateX(-2px);
            }

            97% {
                transform: translateX(2px);
            }
        }

        @keyframes glitch2 {

            0%,
            88%,
            100% {
                transform: translateX(0);
            }

            89% {
                transform: translateX(3px);
            }

            91% {
                transform: translateX(-3px);
            }

            93% {
                transform: translateX(2px);
            }

            95% {
                transform: translateX(-1px);
            }
        }

        /* ── Orbit ── */
        .orbit {
            width: 220px;
            height: 220px;
            position: relative;
            margin: 0 auto 2rem;
            animation: spin 18s linear infinite;
        }

        .planet {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7F77DD, #534AB7);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: inset -12px -8px 0 rgba(0, 0, 0, .3);
        }

        .ring {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 130px;
            height: 40px;
            border: 3px solid rgba(175, 169, 236, .6);
            border-radius: 50%;
            transform: translate(-50%, -50%) rotateX(70deg);
        }

        .satellite {
            width: 18px;
            height: 18px;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            animation: spin-reverse 18s linear infinite;
        }

        .sat-body {
            width: 18px;
            height: 12px;
            background: #FA9E75;
            border-radius: 3px;
            position: relative;
            margin: 0 auto;
        }

        .sat-wing {
            width: 24px;
            height: 6px;
            background: #378ADD;
            border-radius: 2px;
            position: absolute;
            top: 3px;
        }

        .sat-wing.l {
            left: -26px;
        }

        .sat-wing.r {
            right: -26px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spin-reverse {
            to {
                transform: translateX(-50%) rotate(-360deg);
            }
        }

        /* ── Asteroids ── */
        .asteroid {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .25);
            animation: float var(--ad) ease-in-out infinite alternate;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }

            100% {
                transform: translate(var(--ax), var(--ay)) rotate(180deg);
            }
        }

        /* ── Text ── */
        .headline {
            font-size: clamp(20px, 4vw, 28px);
            font-weight: 600;
            color: #fff;
            margin-bottom: .5rem;
            animation: fadeUp .8s ease both .2s;
        }

        .sub {
            font-size: 15px;
            color: rgba(255, 255, 255, .55);
            margin-bottom: 2rem;
            line-height: 1.7;
            animation: fadeUp .8s ease both .4s;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Buttons ── */
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeUp .8s ease both .6s;
        }

        .btn {
            padding: 10px 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: transform .15s, opacity .15s, background .15s;
            border: 1px solid rgba(255, 255, 255, .18);
            background: rgba(255, 255, 255, .07);
            color: #fff;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: rgba(255, 255, 255, .14);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: scale(.97);
        }

        .btn.primary {
            background: #534AB7;
            border-color: #534AB7;
        }

        .btn.primary:hover {
            background: #7F77DD;
            border-color: #7F77DD;
        }

        /* ── Counter ── */
        .counter {
            font-size: 12px;
            color: rgba(255, 255, 255, .3);
            margin-top: 1.5rem;
            letter-spacing: .08em;
            font-family: 'Courier New', monospace;
            animation: fadeUp .8s ease both .8s;
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .actions {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 200px;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <div class="stars" id="stars"></div>
    <div class="scan-line"></div>

    <div class="scene">
        <div class="num" data-text="404">404</div>

        <div class="orbit">
            <div class="planet">
                <div class="ring"></div>
            </div>
            <div class="satellite">
                <div class="sat-body">
                    <div class="sat-wing l"></div>
                    <div class="sat-wing r"></div>
                </div>
            </div>
        </div>

        <div class="headline">Lost in space</div>
        <p class="sub">
            The page you're looking for drifted into a black hole.<br>
            It may have never existed, or moved to another galaxy.
        </p>

        <div class="actions">
            <a href="/" class="btn primary">Take me home</a>
            <a href="javascript:history.back()" class="btn">Go back</a>
            <a href="/contact" class="btn">Report broken link</a>
        </div>

        <div class="counter" id="counter">searching coordinates... 0%</div>
    </div>

    <script>
        /* ── Generate stars ── */
        const starsEl = document.getElementById('stars');

        for (let i = 0; i < 140; i++) {
            const s = document.createElement('div');
            s.className = 'star';
            const size = Math.random() * 3 + 1;
            s.style.cssText = `
                width:${size}px; height:${size}px;
                top:${Math.random() * 100}%;
                left:${Math.random() * 100}%;
                --d:${(Math.random() * 3 + 1.5).toFixed(1)}s;
                animation-delay:${(Math.random() * 3).toFixed(1)}s
            `;
            starsEl.appendChild(s);
        }

        /* ── Generate floating asteroids ── */
        for (let i = 0; i < 14; i++) {
            const a = document.createElement('div');
            a.className = 'asteroid';
            const size = Math.random() * 6 + 3;
            a.style.cssText = `
                width:${size}px; height:${size}px;
                top:${Math.random() * 100}%;
                left:${Math.random() * 100}%;
                --ad:${(Math.random() * 4 + 3).toFixed(1)}s;
                --ax:${(Math.random() * 40 - 20).toFixed(0)}px;
                --ay:${(Math.random() * 40 - 20).toFixed(0)}px;
                animation-delay:${(Math.random() * 3).toFixed(1)}s;
                opacity:${(Math.random() * .5 + .2).toFixed(2)}
            `;
            starsEl.appendChild(a);
        }

        /* ── Fake coordinate counter ── */
        let pct = 0;
        const counter = document.getElementById('counter');
        const id = setInterval(() => {
            pct = Math.min(pct + Math.floor(Math.random() * 7 + 1), 99);
            counter.textContent = `searching coordinates... ${pct}%`;
            if (pct >= 99) {
                clearInterval(id);
                setTimeout(() => {
                    counter.textContent = 'coordinates not found. signal lost.';
                }, 600);
            }
        }, 120);
    </script>
</body>

</html>
