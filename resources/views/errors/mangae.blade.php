<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bangers&family=Noto+Sans+JP:wght@400;700;900&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Noto Sans JP', sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* ── Speed lines background ── */
        .speed-lines {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        /* ── Halftone dots overlay ── */
        .halftone {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, #00000012 1px, transparent 1px);
            background-size: 12px 12px;
        }

        /* ── Panel border effect ── */
        .panel-border {
            position: fixed;
            inset: 0;
            border: 8px solid #000;
            z-index: 10;
            pointer-events: none;
        }
        .panel-border::before {
            content: '';
            position: absolute;
            inset: 6px;
            border: 2px solid #000;
        }

        /* ── Main layout ── */
        .scene {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 900px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
        }

        .panels {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto auto;
            gap: 0;
            width: 100%;
            border: 4px solid #000;
            background: #000;
        }

        /* ── Individual manga panels ── */
        .panel {
            background: #fff;
            position: relative;
            overflow: hidden;
        }

        /* Panel 1 — top left: big 404 with shock lines */
        .panel-1 {
            grid-column: 1;
            grid-row: 1;
            border-right: 4px solid #000;
            border-bottom: 4px solid #000;
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .shock-bg {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .num-404 {
            font-family: 'Bangers', cursive;
            font-size: clamp(90px, 14vw, 140px);
            color: #000;
            letter-spacing: 4px;
            position: relative;
            z-index: 2;
            -webkit-text-stroke: 3px #000;
            paint-order: stroke fill;
            line-height: 1;
        }
        .num-404 span {
            color: #e8000d;
        }

        /* Manga action lines from center */
        .action-lines {
            position: absolute;
            inset: -20px;
            z-index: 1;
        }

        /* Panel 2 — top right: character reaction */
        .panel-2 {
            grid-column: 2;
            grid-row: 1;
            border-bottom: 4px solid #000;
            min-height: 320px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 20px;
            background: #fff;
        }

        /* Manga character — CSS art */
        .character {
            position: relative;
            width: 180px;
            height: 260px;
        }

        /* Head */
        .char-head {
            width: 90px;
            height: 95px;
            background: #ffe8c8;
            border: 3px solid #000;
            border-radius: 45% 45% 40% 40%;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        /* Hair */
        .char-hair {
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 96px;
        }
        .hair-top {
            width: 96px;
            height: 45px;
            background: #1a1a1a;
            border: 3px solid #000;
            border-radius: 50% 50% 0 0;
            position: relative;
        }
        .hair-spike {
            position: absolute;
            background: #1a1a1a;
            border: 2px solid #000;
        }
        .hair-spike.s1 { width: 18px; height: 28px; top: -18px; left: 8px; border-radius: 50% 50% 0 0; transform: rotate(-15deg); }
        .hair-spike.s2 { width: 22px; height: 34px; top: -22px; left: 28px; border-radius: 50% 50% 0 0; }
        .hair-spike.s3 { width: 18px; height: 30px; top: -20px; left: 50px; border-radius: 50% 50% 0 0; transform: rotate(10deg); }
        .hair-spike.s4 { width: 14px; height: 22px; top: -14px; right: 0px; border-radius: 50% 50% 0 0; transform: rotate(20deg); }
        /* Eyes — big manga eyes */
        .char-eyes {
            position: absolute;
            top: 38px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }
        .eye {
            width: 22px;
            height: 26px;
            background: #fff;
            border: 3px solid #000;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
        }
        .eye-pupil {
            width: 14px;
            height: 16px;
            background: #1a1a1a;
            border-radius: 50%;
            position: absolute;
            top: 4px;
            left: 3px;
        }
        .eye-shine {
            width: 5px;
            height: 5px;
            background: #fff;
            border-radius: 50%;
            position: absolute;
            top: 3px;
            left: 2px;
            z-index: 2;
        }
        /* Sweat drop */
        .sweat {
            position: absolute;
            top: 30px;
            right: -18px;
            width: 12px;
            height: 18px;
            background: #6ec6f0;
            border: 2px solid #000;
            border-radius: 50% 50% 50% 50% / 30% 30% 70% 70%;
            transform: rotate(15deg);
        }
        /* Mouth — shocked O */
        .char-mouth {
            position: absolute;
            top: 68px;
            left: 50%;
            transform: translateX(-50%);
            width: 18px;
            height: 18px;
            background: #8B0000;
            border: 3px solid #000;
            border-radius: 50%;
        }
        /* Blush marks */
        .blush {
            position: absolute;
            top: 60px;
            width: 18px;
            height: 7px;
            background: #ffaaaa;
            border-radius: 50%;
            opacity: .7;
        }
        .blush.l { left: 4px; }
        .blush.r { right: 4px; }
        /* Body */
        .char-body {
            position: absolute;
            top: 88px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 90px;
            background: #1a1a1a;
            border: 3px solid #000;
            border-radius: 8px 8px 4px 4px;
        }
        /* Collar */
        .char-collar {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 14px solid transparent;
            border-right: 14px solid transparent;
            border-top: 22px solid #e8000d;
        }
        /* Arms */
        .char-arm {
            position: absolute;
            width: 20px;
            height: 75px;
            background: #1a1a1a;
            border: 3px solid #000;
            border-radius: 10px;
            top: 88px;
        }
        .char-arm.l {
            left: calc(50% - 55px);
            transform: rotate(25deg);
            transform-origin: top center;
        }
        .char-arm.r {
            right: calc(50% - 55px);
            transform: rotate(-25deg);
            transform-origin: top center;
        }
        /* Legs */
        .char-leg {
            position: absolute;
            width: 24px;
            height: 70px;
            background: #2d2d2d;
            border: 3px solid #000;
            border-radius: 4px;
            top: 170px;
        }
        .char-leg.l { left: calc(50% - 32px); }
        .char-leg.r { right: calc(50% - 32px); }

        /* ── Speech bubble panel-2 ── */
        .speech-bubble {
            position: absolute;
            top: 20px;
            left: 16px;
            background: #fff;
            border: 3px solid #000;
            border-radius: 16px;
            padding: 10px 14px;
            max-width: 140px;
            z-index: 3;
        }
        .speech-bubble::after {
            content: '';
            position: absolute;
            bottom: -18px;
            right: 28px;
            border: 9px solid transparent;
            border-top: 14px solid #000;
        }
        .speech-bubble::before {
            content: '';
            position: absolute;
            bottom: -12px;
            right: 30px;
            border: 7px solid transparent;
            border-top: 11px solid #fff;
            z-index: 1;
        }
        .bubble-text {
            font-size: 12px;
            font-weight: 900;
            line-height: 1.4;
            color: #000;
        }
        .bubble-jp {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }

        /* ── Panel 3 — bottom left: error message ── */
        .panel-3 {
            grid-column: 1;
            grid-row: 2;
            border-right: 4px solid #000;
            min-height: 200px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
            background: #fff;
        }

        .impact-text {
            font-family: 'Bangers', cursive;
            font-size: clamp(26px, 4vw, 36px);
            color: #000;
            letter-spacing: 3px;
            line-height: 1.1;
            position: relative;
            display: inline-block;
        }
        .impact-text.red { color: #e8000d; }

        /* Thought bubble */
        .thought {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 4px;
        }
        .thought-dots {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin-top: 4px;
        }
        .thought-dot {
            width: 6px;
            height: 6px;
            background: #000;
            border-radius: 50%;
        }
        .thought-dot:nth-child(2) { width: 9px; height: 9px; }
        .thought-dot:nth-child(3) { width: 12px; height: 12px; }
        .thought-box {
            background: #fff;
            border: 3px solid #000;
            border-radius: 50px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            color: #555;
            line-height: 1.5;
        }

        /* ── Panel 4 — bottom right: actions ── */
        .panel-4 {
            grid-column: 2;
            grid-row: 2;
            min-height: 200px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
            background: #fff;
        }

        .action-label {
            font-family: 'Bangers', cursive;
            font-size: 13px;
            letter-spacing: 2px;
            color: #888;
            margin-bottom: 4px;
        }

        .manga-btn {
            display: block;
            padding: 11px 20px;
            font-family: 'Bangers', cursive;
            font-size: 20px;
            letter-spacing: 3px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            position: relative;
            transition: transform .1s;
            text-align: center;
            width: 100%;
        }
        .manga-btn:active { transform: translate(3px, 3px); }

        .manga-btn.primary {
            background: #e8000d;
            color: #fff;
            border: 3px solid #000;
            box-shadow: 4px 4px 0 #000;
        }
        .manga-btn.primary:active { box-shadow: none; }

        .manga-btn.secondary {
            background: #fff;
            color: #000;
            border: 3px solid #000;
            box-shadow: 4px 4px 0 #000;
        }
        .manga-btn.secondary:active { box-shadow: none; }

        /* ── SFX text ── */
        .sfx {
            position: absolute;
            font-family: 'Bangers', cursive;
            letter-spacing: 2px;
            pointer-events: none;
            transform-origin: center;
        }

        /* ── Scream lines (panel 1) ── */
        .scream-lines {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        /* ── Panel label tabs ── */
        .panel-tab {
            position: absolute;
            bottom: 8px;
            right: 8px;
            font-size: 10px;
            font-weight: 700;
            color: #aaa;
            letter-spacing: .05em;
        }

        /* ── Responsive ── */
        @media (max-width: 560px) {
            .panels {
                grid-template-columns: 1fr;
                grid-template-rows: auto;
            }
            .panel-1, .panel-2 { grid-column: 1; }
            .panel-1 { grid-row: 1; border-right: none; }
            .panel-2 { grid-row: 2; border-bottom: 4px solid #000; }
            .panel-3 { grid-column: 1; grid-row: 3; border-right: none; }
            .panel-4 { grid-column: 1; grid-row: 4; }
        }

        /* ── Animations ── */
        @keyframes shake {
            0%,100%{ transform: translateX(0) }
            20%{ transform: translateX(-4px) rotate(-1deg) }
            40%{ transform: translateX(4px) rotate(1deg) }
            60%{ transform: translateX(-3px) }
            80%{ transform: translateX(3px) }
        }
        .num-404 { animation: shake 3s ease-in-out infinite; }

        @keyframes pulse-sfx {
            0%,100%{ transform: scale(1) rotate(-8deg); }
            50%{ transform: scale(1.08) rotate(-8deg); }
        }

        @keyframes bob {
            0%,100%{ transform: translateY(0); }
            50%{ transform: translateY(-6px); }
        }
        .character { animation: bob 2.5s ease-in-out infinite; }
    </style>
</head>
<body>

    <div class="halftone"></div>
    <div class="panel-border"></div>

    <div class="scene">
        <div class="panels">

            <!-- Panel 1: Big 404 with speed lines -->
            <div class="panel panel-1">

                <!-- SVG speed lines -->
                <svg class="action-lines" viewBox="0 0 400 320" xmlns="http://www.w3.org/2000/svg">
                    <g opacity="0.12">
                        <line x1="200" y1="160" x2="0"   y2="0"   stroke="#000" stroke-width="2"/>
                        <line x1="200" y1="160" x2="80"  y2="0"   stroke="#000" stroke-width="1.5"/>
                        <line x1="200" y1="160" x2="160" y2="0"   stroke="#000" stroke-width="1"/>
                        <line x1="200" y1="160" x2="240" y2="0"   stroke="#000" stroke-width="1"/>
                        <line x1="200" y1="160" x2="320" y2="0"   stroke="#000" stroke-width="1.5"/>
                        <line x1="200" y1="160" x2="400" y2="0"   stroke="#000" stroke-width="2"/>
                        <line x1="200" y1="160" x2="400" y2="80"  stroke="#000" stroke-width="1.5"/>
                        <line x1="200" y1="160" x2="400" y2="160" stroke="#000" stroke-width="1"/>
                        <line x1="200" y1="160" x2="400" y2="240" stroke="#000" stroke-width="1.5"/>
                        <line x1="200" y1="160" x2="400" y2="320" stroke="#000" stroke-width="2"/>
                        <line x1="200" y1="160" x2="320" y2="320" stroke="#000" stroke-width="1.5"/>
                        <line x1="200" y1="160" x2="240" y2="320" stroke="#000" stroke-width="1"/>
                        <line x1="200" y1="160" x2="160" y2="320" stroke="#000" stroke-width="1"/>
                        <line x1="200" y1="160" x2="80"  y2="320" stroke="#000" stroke-width="1.5"/>
                        <line x1="200" y1="160" x2="0"   y2="320" stroke="#000" stroke-width="2"/>
                        <line x1="200" y1="160" x2="0"   y2="240" stroke="#000" stroke-width="1.5"/>
                        <line x1="200" y1="160" x2="0"   y2="160" stroke="#000" stroke-width="1"/>
                        <line x1="200" y1="160" x2="0"   y2="80"  stroke="#000" stroke-width="1.5"/>
                    </g>
                </svg>

                <!-- Red impact burst behind number -->
                <svg style="position:absolute;inset:0;z-index:1;width:100%;height:100%" viewBox="0 0 400 320">
                    <polygon points="200,60 220,130 290,100 240,160 310,180 230,190 260,260 200,220 140,260 170,190 90,180 160,160 110,100 180,130" fill="#e8000d" opacity="0.1"/>
                </svg>

                <div class="num-404" style="position:relative;z-index:3">4<span>0</span>4</div>

                <!-- SFX: DOOOM -->
                <div class="sfx" style="top:18px;left:16px;font-size:28px;color:#e8000d;transform:rotate(-8deg);opacity:.9;animation:pulse-sfx 2s ease-in-out infinite">DOOOM!!</div>

                <div class="panel-tab">p.1</div>
            </div>

            <!-- Panel 2: Shocked character -->
            <div class="panel panel-2">

                <!-- Diagonal stripe bg top corner -->
                <svg style="position:absolute;top:0;right:0;width:100%;height:100%;z-index:0;opacity:.04" viewBox="0 0 300 320" preserveAspectRatio="none">
                    <line x1="0"   y1="0"   x2="300" y2="0"   stroke="#000" stroke-width="18"/>
                    <line x1="0"   y1="36"  x2="300" y2="36"  stroke="#000" stroke-width="18"/>
                    <line x1="0"   y1="72"  x2="300" y2="72"  stroke="#000" stroke-width="18"/>
                    <line x1="0"   y1="108" x2="300" y2="108" stroke="#000" stroke-width="18"/>
                    <line x1="0"   y1="144" x2="300" y2="144" stroke="#000" stroke-width="18"/>
                    <line x1="0"   y1="180" x2="300" y2="180" stroke="#000" stroke-width="18"/>
                    <line x1="0"   y1="216" x2="300" y2="216" stroke="#000" stroke-width="18"/>
                    <line x1="0"   y1="252" x2="300" y2="252" stroke="#000" stroke-width="18"/>
                    <line x1="0"   y1="288" x2="300" y2="288" stroke="#000" stroke-width="18"/>
                </svg>

                <!-- Speech bubble -->
                <div class="speech-bubble">
                    <div class="bubble-text">W-WHAT?! THE PAGE IS GONE!!</div>
                    <div class="bubble-jp">そんな...！！</div>
                </div>

                <!-- CSS Character -->
                <div class="character">
                    <div class="char-hair">
                        <div class="hair-top">
                            <div class="hair-spike s1"></div>
                            <div class="hair-spike s2"></div>
                            <div class="hair-spike s3"></div>
                            <div class="hair-spike s4"></div>
                        </div>
                    </div>
                    <div class="char-head">
                        <div class="char-eyes">
                            <div class="eye"><div class="eye-pupil"></div><div class="eye-shine"></div></div>
                            <div class="eye"><div class="eye-pupil"></div><div class="eye-shine"></div></div>
                        </div>
                        <div class="sweat"></div>
                        <div class="blush l"></div>
                        <div class="blush r"></div>
                        <div class="char-mouth"></div>
                    </div>
                    <div class="char-arm l"></div>
                    <div class="char-arm r"></div>
                    <div class="char-body"><div class="char-collar"></div></div>
                    <div class="char-leg l"></div>
                    <div class="char-leg r"></div>
                </div>

                <!-- SFX -->
                <div class="sfx" style="bottom:16px;right:12px;font-size:22px;color:#000;transform:rotate(6deg);opacity:.6">NANI?!</div>

                <div class="panel-tab">p.2</div>
            </div>

            <!-- Panel 3: Error description -->
            <div class="panel panel-3">

                <div>
                    <div class="impact-text">THE PAGE YOU SEEK</div><br>
                    <div class="impact-text red">HAS VANISHED</div><br>
                    <div class="impact-text">INTO THE VOID.</div>
                </div>

                <div class="thought">
                    <div class="thought-dots">
                        <div class="thought-dot"></div>
                        <div class="thought-dot"></div>
                        <div class="thought-dot"></div>
                    </div>
                    <div class="thought-box">
                        Maybe it was deleted...<br>or never existed at all.
                    </div>
                </div>

                <!-- SFX watermark -->
                <div class="sfx" style="bottom:10px;left:12px;font-size:48px;color:#000;opacity:.04;transform:rotate(-5deg)">ERROR</div>

                <div class="panel-tab">p.3</div>
            </div>

            <!-- Panel 4: Actions -->
            <div class="panel panel-4">

                <div class="action-label">— YOUR NEXT MOVE —</div>

                <a href="/" class="manga-btn primary">
                    &#9654; RETURN HOME
                </a>

                <a href="javascript:history.back()" class="manga-btn secondary">
                    &#8592; GO BACK
                </a>

                <!-- Tiny author note style -->
                <div style="font-size:11px;color:#aaa;margin-top:8px;font-style:italic;text-align:center">
                    ページが見つかりませんでした — vol.404, ch.1
                </div>

                <div class="panel-tab">p.4</div>
            </div>

        </div>
    </div>

</body>
</html>