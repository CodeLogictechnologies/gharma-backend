<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Nani?!</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bangers&family=Noto+Sans+JP:wght@900&display=swap');

        :root {
            --op-blue: #0a2a6e;
            --op-gold: #f5c842;
            --op-red: #e8003d;
            --na-orange: #ff6b1a;
            --na-yellow: #f5c842;
            --na-dark: #1a1200;
            --ink: #111;
            --paper: #fdf8ee;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            background: var(--paper);
            font-family: 'Noto Sans JP', sans-serif;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
            position: relative;
            cursor: none;
        }

        /* ── Custom cursor: crosshair manga style ── */
        .cursor {
            position: fixed;
            width: 28px;
            height: 28px;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: transform .1s;
        }

        .cursor::before,
        .cursor::after {
            content: '';
            position: absolute;
            background: var(--op-red);
        }

        .cursor::before {
            width: 2px;
            height: 28px;
            top: 0;
            left: 13px
        }

        .cursor::after {
            width: 28px;
            height: 2px;
            top: 13px;
            left: 0
        }

        .cursor-ring {
            position: fixed;
            width: 44px;
            height: 44px;
            border: 2px solid var(--ink);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9998;
            transform: translate(-50%, -50%);
            transition: all .15s ease;
        }

        /* ── Halftone overlay ── */
        .halftone {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background-image: radial-gradient(circle, #00000009 1px, transparent 1px);
            background-size: 10px 10px;
        }

        /* ── Ink splatter particles ── */
        .splatter {
            position: fixed;
            border-radius: 50%;
            background: var(--ink);
            pointer-events: none;
            z-index: 2;
            animation: splat .6s ease-out forwards;
        }

        @keyframes splat {
            0% {
                transform: scale(0);
                opacity: 1
            }

            70% {
                opacity: .6
            }

            100% {
                transform: scale(1);
                opacity: 0
            }
        }

        /* ── Page border double ink ── */
        .border-frame {
            position: fixed;
            inset: 0;
            z-index: 200;
            pointer-events: none;
            border: 10px solid var(--ink);
        }

        .border-frame::before {
            content: '';
            position: absolute;
            inset: 7px;
            border: 2px solid var(--ink);
        }

        .border-frame::after {
            content: '';
            position: absolute;
            inset: 14px;
            border: .5px solid rgba(0, 0, 0, .15);
        }

        /* ═══════════════════════════════
   MAIN LAYOUT
═══════════════════════════════ */
        .stage {
            position: relative;
            z-index: 10;
            width: 100%;
            height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr auto;
            gap: 0;
            border: 5px solid var(--ink);
            margin: 20px;
            width: calc(100vw - 40px);
            height: calc(100vh - 40px);
            background: var(--ink);
            overflow: hidden;
        }

        /* ══ LEFT PANEL: ONE PIECE ══ */
        .panel-op {
            position: relative;
            overflow: hidden;
            background: var(--op-blue);
            border-right: 5px solid var(--ink);
            border-bottom: 5px solid var(--ink);
        }

        /* Animated ocean */
        .ocean {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 45%;
            overflow: hidden
        }

        .ocean-layer {
            position: absolute;
            left: -50%;
            width: 200%;
            border-radius: 50% 50% 0 0;
            animation: ocean-drift 5s ease-in-out infinite;
        }

        .ol1 {
            background: #1040a0;
            height: 120%;
            bottom: -30%;
            animation-delay: 0s
        }

        .ol2 {
            background: #0d3080;
            height: 115%;
            bottom: -40%;
            opacity: .7;
            animation-delay: -.8s;
            animation-duration: 6s
        }

        .ol3 {
            background: #071f55;
            height: 110%;
            bottom: -50%;
            opacity: .5;
            animation-delay: -1.6s;
            animation-duration: 7s
        }

        @keyframes ocean-drift {

            0%,
            100% {
                transform: translateX(0) scaleY(1)
            }

            50% {
                transform: translateX(3%) scaleY(1.04)
            }
        }

        /* Animated sky gradient */
        .sky {
            position: absolute;
            inset: 0;
            bottom: 45%;
            background: linear-gradient(180deg, #030f2e 0%, #0a2a6e 60%, #1a4aaa 100%);
        }

        /* Stars in sky */
        .stars-op {
            position: absolute;
            inset: 0;
            bottom: 45%
        }

        .star-op {
            position: absolute;
            border-radius: 50%;
            background: #fff;
            animation: star-twinkle var(--sd) ease-in-out infinite alternate;
        }

        @keyframes star-twinkle {
            0% {
                opacity: .2
            }

            100% {
                opacity: 1
            }
        }

        /* Sun / Moon */
        .op-sun {
            position: absolute;
            top: 14%;
            right: 22%;
            width: 68px;
            height: 68px;
            background: var(--op-gold);
            border-radius: 50%;
            border: 4px solid var(--ink);
            box-shadow: 0 0 0 12px rgba(245, 200, 66, .15);
            animation: sun-pulse 4s ease-in-out infinite;
        }

        @keyframes sun-pulse {

            0%,
            100% {
                box-shadow: 0 0 0 12px rgba(245, 200, 66, .15)
            }

            50% {
                box-shadow: 0 0 0 24px rgba(245, 200, 66, .05)
            }
        }

        /* Going Merry ship silhouette SVG */
        .merry {
            position: absolute;
            bottom: 43%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 4;
            animation: ship-bob 4s ease-in-out infinite;
        }

        @keyframes ship-bob {

            0%,
            100% {
                transform: translateX(-50%) translateY(0) rotate(0deg)
            }

            30% {
                transform: translateX(-50%) translateY(-8px) rotate(1.5deg)
            }

            70% {
                transform: translateX(-50%) translateY(-4px) rotate(-1deg)
            }
        }

        /* OP Speech bubble */
        .op-bubble {
            position: absolute;
            top: 16px;
            left: 16px;
            z-index: 20;
            background: #fff;
            border: 4px solid var(--ink);
            border-radius: 18px;
            padding: 10px 14px;
            max-width: 200px;
            animation: bubble-pop .5s cubic-bezier(.17, .67, .35, 1.3) both;
            animation-delay: .3s;
            transform-origin: bottom left;
            transform: scale(0);
        }

        @keyframes bubble-pop {
            to {
                transform: scale(1)
            }
        }

        .op-bubble::after {
            content: '';
            position: absolute;
            bottom: -22px;
            left: 22px;
            border: 10px solid transparent;
            border-top: 18px solid var(--ink);
        }

        .op-bubble::before {
            content: '';
            position: absolute;
            bottom: -14px;
            left: 25px;
            border: 7px solid transparent;
            border-top: 13px solid #fff;
            z-index: 1;
        }

        /* Luffy */
        .luffy-wrap {
            position: absolute;
            bottom: 42%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 5;
            animation: luffy-float 3s ease-in-out infinite;
        }

        @keyframes luffy-float {

            0%,
            100% {
                transform: translateX(-50%) translateY(0)
            }

            50% {
                transform: translateX(-50%) translateY(-12px)
            }
        }

        /* ── Luffy CSS character ── */
        .straw-hat {
            width: 180px;
            height: 32px;
            background: var(--op-gold);
            border: 4px solid var(--ink);
            border-radius: 50%;
            position: absolute;
            top: -22px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 5;
        }

        .straw-hat::before {
            content: '';
            position: absolute;
            top: -36px;
            left: 50%;
            transform: translateX(-50%);
            width: 96px;
            height: 54px;
            background: var(--op-gold);
            border: 4px solid var(--ink);
            border-radius: 50% 50% 40% 40%;
        }

        .straw-hat::after {
            content: '';
            position: absolute;
            top: -18px;
            left: 50%;
            transform: translateX(-50%);
            width: 98px;
            height: 14px;
            background: var(--op-red);
            border-top: 2px solid var(--ink);
            border-bottom: 2px solid var(--ink);
        }

        .l-head {
            width: 108px;
            height: 112px;
            background: #f5cba7;
            border: 4px solid var(--ink);
            border-radius: 46% 46% 42% 42%;
            position: relative;
            margin: 0 auto;
            top: 12px;
            z-index: 4;
        }

        .l-hair-base {
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 112px;
            height: 52px;
            background: var(--ink);
            border: 3px solid var(--ink);
            border-radius: 50% 50% 0 0;
            z-index: 5;
        }

        .l-strand {
            position: absolute;
            bottom: -10px;
            background: var(--ink);
            border-radius: 0 0 6px 6px;
            width: 16px;
        }

        .ls1 {
            height: 22px;
            left: 8px;
            transform: rotate(10deg)
        }

        .ls2 {
            height: 26px;
            left: 26px
        }

        .ls3 {
            height: 20px;
            left: 48px;
            transform: rotate(-5deg)
        }

        .ls4 {
            height: 24px;
            right: 26px
        }

        .ls5 {
            height: 18px;
            right: 8px;
            transform: rotate(-10deg)
        }

        .l-eyes {
            display: flex;
            gap: 20px;
            position: absolute;
            top: 40px;
            left: 50%;
            transform: translateX(-50%)
        }

        .l-eye {
            width: 22px;
            height: 22px;
            background: var(--ink);
            border-radius: 50%;
            position: relative
        }

        .l-eye::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 7px;
            height: 7px;
            background: #fff;
            border-radius: 50%
        }

        .scar-mark {
            position: absolute;
            top: 64px;
            left: 20px;
            width: 11px;
            height: 14px;
            border-bottom: 3px solid var(--op-red);
            border-right: 2px solid var(--op-red);
            border-radius: 0 0 4px 0;
            transform: rotate(-20deg)
        }

        .l-mouth {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            width: 56px;
            height: 24px;
            background: #7a0000;
            border: 3px solid var(--ink);
            border-radius: 0 0 32px 32px;
            overflow: hidden;
        }

        .l-teeth {
            display: flex;
            gap: 2px;
            padding: 2px 5px
        }

        .tooth {
            width: 8px;
            height: 11px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 2px 2px 0 0
        }

        .l-body {
            width: 88px;
            height: 100px;
            background: var(--op-red);
            border: 4px solid var(--ink);
            border-radius: 6px 6px 4px 4px;
            position: relative;
            margin: 0 auto;
            z-index: 3;
        }

        .l-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 22px solid transparent;
            border-right: 22px solid transparent;
            border-top: 30px solid #f5cba7;
        }

        .l-arm {
            position: absolute;
            height: 20px;
            background: #f5cba7;
            border: 3px solid var(--ink);
            border-radius: 10px;
            top: 10px;
        }

        .l-arm.L {
            width: 90px;
            left: -94px;
            transform: rotate(-20deg);
            animation: arm-stretch 3s ease-in-out infinite
        }

        .l-arm.R {
            width: 90px;
            right: -94px;
            transform: rotate(20deg);
            animation: arm-stretch 3s ease-in-out infinite .5s
        }

        @keyframes arm-stretch {

            0%,
            100% {
                transform: rotate(-20deg);
                width: 90px
            }

            50% {
                transform: rotate(-25deg);
                width: 110px
            }
        }

        .l-fist {
            position: absolute;
            width: 28px;
            height: 24px;
            background: #f5cba7;
            border: 3px solid var(--ink);
            border-radius: 6px
        }

        .l-fist.L {
            right: -30px;
            top: -2px
        }

        .l-fist.R {
            left: -30px;
            top: -2px
        }

        .l-legs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 2px
        }

        .l-leg {
            width: 30px;
            height: 70px;
            background: #4a3500;
            border: 4px solid var(--ink);
            border-radius: 4px
        }

        /* SFX labels */
        .sfx {
            position: absolute;
            font-family: 'Bangers', cursive;
            letter-spacing: 2px;
            pointer-events: none
        }

        /* ══ RIGHT PANEL: NARUTO ══ */
        .panel-na {
            position: relative;
            overflow: hidden;
            background: var(--na-dark);
            border-bottom: 5px solid var(--ink);
        }

        /* Forest background */
        .forest-bg {
            position: absolute;
            inset: 0
        }

        .tree {
            position: absolute;
            bottom: 0;
            background: #0a1a00;
            border-radius: 2px 2px 0 0;
        }

        /* Chakra energy rings */
        .chakra-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid var(--na-orange);
            opacity: 0;
            animation: chakra-pulse 3s ease-out infinite;
        }

        @keyframes chakra-pulse {
            0% {
                transform: translate(-50%, -50%) scale(.2);
                opacity: .8
            }

            100% {
                transform: translate(-50%, -50%) scale(2.5);
                opacity: 0
            }
        }

        /* Sky gradient for Naruto */
        .na-sky {
            position: absolute;
            inset: 0;
            bottom: 35%;
            background: linear-gradient(180deg, #0a0a00 0%, #1a0a00 50%, #2a1000 100%);
        }

        /* Moon */
        .na-moon {
            position: absolute;
            top: 12%;
            left: 20%;
            width: 55px;
            height: 55px;
            background: #e8e0c0;
            border-radius: 50%;
            border: 3px solid #c8c090;
            box-shadow: 0 0 20px rgba(232, 224, 192, .3);
            animation: moon-glow 4s ease-in-out infinite;
        }

        @keyframes moon-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(232, 224, 192, .3)
            }

            50% {
                box-shadow: 0 0 40px rgba(232, 224, 192, .6)
            }
        }

        /* Naruto character */
        .naruto-wrap {
            position: absolute;
            bottom: 34%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 5;
            animation: naruto-run 0s;
        }

        /* ── Naruto CSS character ── */
        .na-headband {
            position: absolute;
            top: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 24px;
            background: #4a4a6a;
            border: 3px solid var(--ink);
            border-radius: 4px 4px 0 0;
            z-index: 6;
        }

        .na-headband::after {
            content: '木';
            position: absolute;
            top: 1px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 13px;
            color: #c8c8e0;
            font-weight: 900;
        }

        .na-hair {
            position: absolute;
            top: -22px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            z-index: 5;
        }

        .na-hair-base {
            width: 100px;
            height: 48px;
            background: var(--na-yellow);
            border: 3px solid var(--ink);
            border-radius: 50% 50% 0 0;
        }

        .na-spike {
            position: absolute;
            background: var(--na-yellow);
            border: 3px solid var(--ink);
            border-radius: 40% 40% 0 0
        }

        .ns1 {
            width: 22px;
            height: 36px;
            top: -30px;
            left: 2px;
            transform: rotate(-22deg)
        }

        .ns2 {
            width: 24px;
            height: 42px;
            top: -38px;
            left: 20px;
            transform: rotate(-10deg)
        }

        .ns3 {
            width: 22px;
            height: 38px;
            top: -34px;
            left: 40px
        }

        .ns4 {
            width: 24px;
            height: 36px;
            top: -32px;
            left: 58px;
            transform: rotate(10deg)
        }

        .ns5 {
            width: 20px;
            height: 28px;
            top: -24px;
            right: 0px;
            transform: rotate(22deg)
        }

        .na-head {
            width: 96px;
            height: 100px;
            background: #f5cba7;
            border: 4px solid var(--ink);
            border-radius: 44% 44% 40% 40%;
            position: relative;
            margin: 0 auto;
            top: 12px;
            z-index: 4;
        }

        .na-eye {
            width: 20px;
            height: 18px;
            background: #4a8fdb;
            border: 3px solid var(--ink);
            border-radius: 50%;
            position: relative
        }

        .na-eye::before {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 7px;
            height: 7px;
            background: var(--ink);
            border-radius: 50%
        }

        .na-eye::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 4px;
            height: 4px;
            background: #fff;
            border-radius: 50%
        }

        .na-eyes {
            display: flex;
            gap: 16px;
            position: absolute;
            top: 38px;
            left: 50%;
            transform: translateX(-50%)
        }

        .na-brow {
            position: absolute;
            top: 28px;
            width: 20px;
            height: 5px;
            background: #d4941e;
            border-radius: 2px;
            border: 1px solid var(--ink)
        }

        .na-brow.L {
            left: 12px;
            transform: rotate(14deg)
        }

        .na-brow.R {
            right: 12px;
            transform: rotate(-14deg)
        }

        .na-whisker {
            position: absolute;
            height: 2px;
            background: #b8860b;
            border-radius: 2px
        }

        .wl1 {
            width: 22px;
            top: 52px;
            left: 4px;
            transform: rotate(-8deg)
        }

        .wl2 {
            width: 22px;
            top: 58px;
            left: 2px
        }

        .wl3 {
            width: 22px;
            top: 64px;
            left: 4px;
            transform: rotate(8deg)
        }

        .wr1 {
            width: 22px;
            top: 52px;
            right: 4px;
            transform: rotate(8deg)
        }

        .wr2 {
            width: 22px;
            top: 58px;
            right: 2px
        }

        .wr3 {
            width: 22px;
            top: 64px;
            right: 4px;
            transform: rotate(-8deg)
        }

        .na-mouth {
            position: absolute;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 5px;
            background: #7a0000;
            border-radius: 2px
        }

        .na-body {
            width: 84px;
            height: 98px;
            background: var(--na-orange);
            border: 4px solid var(--ink);
            border-radius: 6px 6px 4px 4px;
            position: relative;
            margin: 0 auto;
            z-index: 3;
        }

        .na-body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 28px;
            height: 38px;
            background: #f5cba7;
            border-left: 2px solid var(--ink);
            border-right: 2px solid var(--ink);
        }

        .na-body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 38px;
            background: var(--ink)
        }

        .na-arm {
            width: 20px;
            height: 72px;
            background: var(--na-orange);
            border: 3px solid var(--ink);
            border-radius: 10px;
            position: absolute;
            top: 0
        }

        .na-arm.L {
            left: -22px;
            animation: na-arm-move 2s ease-in-out infinite
        }

        .na-arm.R {
            right: -22px;
            animation: na-arm-move 2s ease-in-out infinite .5s
        }

        @keyframes na-arm-move {

            0%,
            100% {
                transform: rotate(10deg)
            }

            50% {
                transform: rotate(-10deg)
            }
        }

        .na-hand {
            width: 24px;
            height: 22px;
            background: #f5cba7;
            border: 3px solid var(--ink);
            border-radius: 5px;
            position: absolute
        }

        .na-hand.L {
            bottom: -24px
        }

        .na-hand.R {
            bottom: -24px
        }

        /* Rasengan effect */
        .rasengan {
            position: absolute;
            bottom: -24px;
            right: -50px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 3px solid var(--na-orange);
            background: rgba(255, 107, 26, .2);
            z-index: 10;
            animation: rasengan-spin 0.4s linear infinite;
            box-shadow: 0 0 12px var(--na-orange);
        }

        .rasengan::before {
            content: '';
            position: absolute;
            inset: 4px;
            border-radius: 50%;
            border: 2px solid #fff;
            animation: rasengan-spin 0.2s linear infinite reverse;
        }

        @keyframes rasengan-spin {
            to {
                transform: rotate(360deg)
            }
        }

        .na-legs {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 2px
        }

        .na-leg {
            width: 28px;
            height: 68px;
            background: #2d2d5a;
            border: 4px solid var(--ink);
            border-radius: 4px
        }

        /* Naruto speech bubble */
        .na-bubble {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 20;
            background: #fff;
            border: 4px solid var(--ink);
            border-radius: 18px;
            padding: 10px 14px;
            max-width: 190px;
            animation: bubble-pop .5s cubic-bezier(.17, .67, .35, 1.3) both;
            animation-delay: .5s;
            transform-origin: bottom right;
            transform: scale(0);
        }

        .na-bubble::after {
            content: '';
            position: absolute;
            bottom: -22px;
            right: 22px;
            border: 10px solid transparent;
            border-top: 18px solid var(--ink);
        }

        .na-bubble::before {
            content: '';
            position: absolute;
            bottom: -14px;
            right: 25px;
            border: 7px solid transparent;
            border-top: 13px solid #fff;
            z-index: 1;
        }

        /* ══ BOTTOM BAR ══ */
        .bottom-bar {
            grid-column: 1/3;
            background: var(--paper);
            border-top: 0px solid var(--ink);
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 0;
            min-height: 180px;
        }

        .bar-op {
            border-right: 5px solid var(--ink);
            padding: 20px 28px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 10px;
            background: rgba(10, 42, 110, .04);
        }

        .bar-center {
            padding: 20px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 240px;
            position: relative;
            overflow: hidden;
        }

        .bar-na {
            border-left: 5px solid var(--ink);
            padding: 20px 28px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 10px;
            background: rgba(255, 107, 26, .04);
        }

        /* The big 404 */
        .giant-404 {
            font-family: 'Bangers', cursive;
            font-size: clamp(52px, 8vw, 84px);
            letter-spacing: 6px;
            line-height: 1;
            position: relative;
            display: inline-block;
            animation: glitch-404 4s ease-in-out infinite;
        }

        @keyframes glitch-404 {

            0%,
            89%,
            100% {
                text-shadow: none;
                transform: none
            }

            90% {
                text-shadow: 3px 0 var(--op-red), -3px 0 var(--na-orange);
                transform: skewX(-2deg)
            }

            92% {
                text-shadow: -4px 0 var(--op-blue), 4px 0 var(--na-orange);
                transform: skewX(2deg)
            }

            94% {
                text-shadow: 2px 0 var(--na-orange), -2px 0 var(--op-red)
            }

            96% {
                text-shadow: none;
                transform: none
            }
        }

        .g4-op {
            color: var(--op-blue);
            -webkit-text-stroke: 2px var(--ink)
        }

        .g0 {
            color: var(--op-red);
            -webkit-text-stroke: 2px var(--ink)
        }

        .g4-na {
            color: var(--na-orange);
            -webkit-text-stroke: 2px var(--ink)
        }

        .vs-pill {
            font-family: 'Bangers', cursive;
            font-size: 13px;
            letter-spacing: 3px;
            background: var(--ink);
            color: #fff;
            padding: 4px 14px;
            border-radius: 4px;
        }

        .center-sub {
            font-size: 11px;
            color: #888;
            text-align: center;
            line-height: 1.7;
            font-weight: 700
        }

        .center-jp {
            font-size: 10px;
            color: #bbb;
            text-align: center
        }

        /* Section heading */
        .sec-label {
            font-family: 'Bangers', cursive;
            font-size: 11px;
            letter-spacing: 3px;
            color: #bbb;
            margin-bottom: 2px;
        }

        /* Manga buttons */
        .manga-btn {
            display: block;
            padding: 11px 20px;
            font-family: 'Bangers', cursive;
            font-size: 18px;
            letter-spacing: 3px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            text-align: center;
            width: 100%;
            transition: transform .1s, box-shadow .1s;
            position: relative;
            overflow: hidden;
        }

        .manga-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: #fff;
            transform: translateX(-100%);
            transition: transform .3s ease;
            opacity: .15;
        }

        .manga-btn:hover::before {
            transform: translateX(0)
        }

        .manga-btn:active {
            transform: translate(4px, 4px) !important
        }

        .btn-op {
            background: var(--op-blue);
            color: var(--op-gold);
            border: 3px solid var(--ink);
            box-shadow: 5px 5px 0 var(--ink);
        }

        .btn-op:hover {
            transform: translate(-2px, -2px);
            box-shadow: 7px 7px 0 var(--ink)
        }

        .btn-op:active {
            box-shadow: none
        }

        .btn-na {
            background: var(--na-orange);
            color: #fff;
            border: 3px solid var(--ink);
            box-shadow: 5px 5px 0 var(--ink);
        }

        .btn-na:hover {
            transform: translate(-2px, -2px);
            box-shadow: 7px 7px 0 var(--ink)
        }

        .btn-na:active {
            box-shadow: none
        }

        .btn-ghost {
            background: transparent;
            color: var(--ink);
            border: 3px solid var(--ink);
            box-shadow: 5px 5px 0 var(--ink);
            font-size: 15px;
        }

        .btn-ghost:hover {
            transform: translate(-2px, -2px);
            box-shadow: 7px 7px 0 var(--ink)
        }

        .btn-ghost:active {
            box-shadow: none
        }

        .page-note {
            font-size: 10px;
            color: #aaa;
            font-style: italic;
            text-align: center;
            margin-top: 2px;
            line-height: 1.7;
        }

        /* ── Animated speed lines across full page ── */
        .speed-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            opacity: 0;
            transition: opacity .3s
        }

        .speed-canvas.active {
            opacity: 1
        }

        /* ── Click impact burst ── */
        .impact {
            position: fixed;
            pointer-events: none;
            z-index: 300;
            font-family: 'Bangers', cursive;
            font-size: 32px;
            letter-spacing: 2px;
            color: var(--op-red);
            -webkit-text-stroke: 1px var(--ink);
            animation: impact-fly .7s ease-out forwards;
            white-space: nowrap;
        }

        @keyframes impact-fly {
            0% {
                transform: translate(-50%, -50%) scale(.5) rotate(-10deg);
                opacity: 1
            }

            100% {
                transform: translate(-50%, -120%) scale(1.2) rotate(5deg);
                opacity: 0
            }
        }

        /* ── Floating kanji ── */
        .kanji {
            position: fixed;
            pointer-events: none;
            z-index: 3;
            font-family: 'Noto Sans JP', sans-serif;
            font-weight: 900;
            color: rgba(0, 0, 0, .06);
            font-size: 80px;
            animation: kanji-drift var(--kd) linear infinite;
            top: var(--ky);
            left: var(--kx);
        }

        @keyframes kanji-drift {
            0% {
                transform: translateY(0) rotate(var(--kr))
            }

            100% {
                transform: translateY(-110vh) rotate(calc(var(--kr) + 20deg))
            }
        }

        /* ── Manga panel decorative number ── */
        .panel-num {
            position: absolute;
            bottom: 8px;
            right: 10px;
            font-family: 'Bangers', cursive;
            font-size: 11px;
            letter-spacing: .05em;
            color: rgba(255, 255, 255, .25);
        }

        .panel-num.dark {
            color: rgba(0, 0, 0, .2)
        }
    </style>
</head>

<body>

    <div class="halftone"></div>
    <div class="border-frame"></div>
    <div class="cursor" id="cur"></div>
    <div class="cursor-ring" id="curRing"></div>
    <canvas class="speed-canvas" id="speedCanvas"></canvas>

    <!-- Floating kanji -->
    <div class="kanji" style="--kd:18s;--ky:90vh;--kx:5vw;--kr:-8deg">海</div>
    <div class="kanji" style="--kd:22s;--ky:80vh;--kx:25vw;--kr:5deg;animation-delay:-6s">忍</div>
    <div class="kanji" style="--kd:16s;--ky:95vh;--kx:55vw;--kr:-12deg;animation-delay:-3s">夢</div>
    <div class="kanji" style="--kd:24s;--ky:85vh;--kx:75vw;--kr:9deg;animation-delay:-10s">力</div>
    <div class="kanji" style="--kd:20s;--ky:88vh;--kx:88vw;--kr:-5deg;animation-delay:-8s">風</div>

    <div class="stage">

        <!-- ═══ LEFT: ONE PIECE ═══ -->
        <div class="panel-op">
            <div class="sky"></div>
            <div class="stars-op" id="starsOp"></div>
            <div class="op-sun"></div>
            <div class="ocean">
                <div class="ocean-layer ol1"></div>
                <div class="ocean-layer ol2"></div>
                <div class="ocean-layer ol3"></div>
            </div>

            <!-- Speed lines SVG -->
            <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:.1;z-index:2" viewBox="0 0 500 600"
                preserveAspectRatio="none">
                <line x1="250" y1="300" x2="0" y2="0" stroke="#fff" stroke-width="3" />
                <line x1="250" y1="300" x2="125" y2="0" stroke="#fff" stroke-width="1.5" />
                <line x1="250" y1="300" x2="250" y2="0" stroke="#fff" stroke-width="1.5" />
                <line x1="250" y1="300" x2="375" y2="0" stroke="#fff" stroke-width="1.5" />
                <line x1="250" y1="300" x2="500" y2="0" stroke="#fff" stroke-width="3" />
                <line x1="250" y1="300" x2="500" y2="150" stroke="#fff" stroke-width="1.5" />
                <line x1="250" y1="300" x2="500" y2="300" stroke="#fff" stroke-width="1.5" />
                <line x1="250" y1="300" x2="500" y2="450" stroke="#fff" stroke-width="1.5" />
                <line x1="250" y1="300" x2="500" y2="600" stroke="#fff" stroke-width="3" />
                <line x1="250" y1="300" x2="375" y2="600" stroke="#fff" stroke-width="1.5" />
                <line x1="250" y1="300" x2="250" y2="600" stroke="#fff" stroke-width="1.5" />
                <line x1="250" y1="300" x2="125" y2="600" stroke="#fff"
                    stroke-width="1.5" />
                <line x1="250" y1="300" x2="0" y2="600" stroke="#fff"
                    stroke-width="3" />
                <line x1="250" y1="300" x2="0" y2="450" stroke="#fff"
                    stroke-width="1.5" />
                <line x1="250" y1="300" x2="0" y2="300" stroke="#fff"
                    stroke-width="1.5" />
                <line x1="250" y1="300" x2="0" y2="150" stroke="#fff"
                    stroke-width="1.5" />
            </svg>

            <!-- Red impact burst -->
            <svg style="position:absolute;top:50%;left:50%;transform:translate(-50%,-55%);z-index:3;width:300px;height:300px;opacity:.18"
                viewBox="0 0 300 300">
                <polygon
                    points="150,10 168,110 240,50 185,135 280,130 200,175 285,220 190,208 230,295 150,235 70,295 110,208 15,220 100,175 20,130 115,135 60,50 132,110"
                    fill="var(--op-gold)" />
            </svg>

            <!-- Speech bubble -->
            <div class="op-bubble">
                <div style="font-size:13px;font-weight:900;color:var(--ink);line-height:1.4">
                    OI! THE PAGE<br>WENT MISSING!!<br>I'LL FIND IT!
                </div>
                <div style="font-size:10px;color:var(--op-red);font-weight:700;margin-top:3px">俺は海賊王になる！</div>
            </div>

            <!-- Luffy -->
            <div class="luffy-wrap">
                <div style="position:relative;width:200px">
                    <div class="straw-hat"></div>
                    <div style="position:absolute;top:8px;left:50%;transform:translateX(-50%);width:116px;z-index:5">
                        <div class="l-hair-base">
                            <div class="l-strand ls1"></div>
                            <div class="l-strand ls2"></div>
                            <div class="l-strand ls3"></div>
                            <div class="l-strand ls4"></div>
                            <div class="l-strand ls5"></div>
                        </div>
                    </div>
                    <div class="l-head">
                        <div class="l-eyes">
                            <div class="l-eye"></div>
                            <div class="l-eye"></div>
                        </div>
                        <div class="scar-mark"></div>
                        <div class="l-mouth">
                            <div class="l-teeth">
                                <div class="tooth"></div>
                                <div class="tooth"></div>
                                <div class="tooth"></div>
                                <div class="tooth"></div>
                                <div class="tooth"></div>
                            </div>
                        </div>
                    </div>
                    <div style="position:relative;margin-top:2px">
                        <div class="l-arm L">
                            <div class="l-fist L"></div>
                        </div>
                        <div class="l-arm R">
                            <div class="l-fist R"></div>
                        </div>
                        <div class="l-body"></div>
                    </div>
                    <div class="l-legs">
                        <div class="l-leg"></div>
                        <div class="l-leg"></div>
                    </div>
                </div>
            </div>

            <!-- Going Merry silhouette -->
            <svg class="merry" width="180" height="80" viewBox="0 0 180 80">
                <path d="M10,70 Q90,30 170,70 L170,78 L10,78Z" fill="#2a1a00" stroke="var(--ink)" stroke-width="3" />
                <path d="M90,70 L90,10 L110,30 L90,32Z" fill="#f5cba7" stroke="var(--ink)" stroke-width="2" />
                <path d="M90,32 L90,70 L70,50Z" fill="#e8003d" stroke="var(--ink)" stroke-width="2" />
                <circle cx="90" cy="10" r="5" fill="var(--op-gold)" stroke="var(--ink)"
                    stroke-width="2" />
            </svg>

            <div class="sfx"
                style="bottom:46%;left:10px;font-size:44px;color:var(--op-gold);-webkit-text-stroke:2px var(--ink);transform:rotate(-12deg);z-index:6;animation:pulse-sfx 2s ease-in-out infinite">
                GOMU!!</div>
            <div class="sfx"
                style="top:44%;right:8px;font-size:19px;color:#fff;opacity:.5;transform:rotate(6deg);z-index:6">
                GUM-GUM<br>PISTOL!!</div>
            @keyframes pulse-sfx{0%,100%{transform:rotate(-12deg) scale(1)}50%{transform:rotate(-12deg) scale(1.07)}}

            <div class="panel-num">p.1 — ONE PIECE</div>
        </div>

        <!-- ═══ RIGHT: NARUTO ═══ -->
        <div class="panel-na">
            <div class="na-sky"></div>
            <div class="na-moon"></div>

            <!-- Forest trees -->
            <div class="forest-bg">
                <div class="tree"
                    style="width:40px;height:180px;left:5%;background:#0a1a00;border-radius:2px 2px 0 0"></div>
                <div class="tree"
                    style="width:55px;height:220px;left:15%;background:#0d2000;border-radius:2px 2px 0 0"></div>
                <div class="tree"
                    style="width:35px;height:160px;left:28%;background:#071400;border-radius:2px 2px 0 0"></div>
                <div class="tree"
                    style="width:60px;height:250px;right:10%;background:#0a1a00;border-radius:2px 2px 0 0"></div>
                <div class="tree"
                    style="width:45px;height:200px;right:22%;background:#0d2000;border-radius:2px 2px 0 0"></div>
                <div class="tree"
                    style="width:38px;height:175px;right:36%;background:#071400;border-radius:2px 2px 0 0"></div>
            </div>

            <!-- Chakra rings -->
            <div class="chakra-ring" style="width:60px;height:60px;top:50%;left:50%;animation-delay:0s"></div>
            <div class="chakra-ring" style="width:60px;height:60px;top:50%;left:50%;animation-delay:1s"></div>
            <div class="chakra-ring" style="width:60px;height:60px;top:50%;left:50%;animation-delay:2s"></div>

            <!-- Naruto speech bubble -->
            <div class="na-bubble">
                <div style="font-size:12px;font-weight:900;color:var(--ink);line-height:1.4">
                    DATTEBAYO!!<br>I'LL NEVER GIVE<br>UP ON THIS PAGE!
                </div>
                <div style="font-size:10px;color:var(--na-orange);font-weight:700;margin-top:3px">信じてくれ！！絶対に！</div>
            </div>

            <!-- Naruto character -->
            <div class="naruto-wrap">
                <div style="position:relative;width:180px">
                    <div style="position:absolute;top:6px;left:50%;transform:translateX(-50%);z-index:5">
                        <div class="na-hair">
                            <div class="na-hair-base">
                                <div class="na-spike ns1"></div>
                                <div class="na-spike ns2"></div>
                                <div class="na-spike ns3"></div>
                                <div class="na-spike ns4"></div>
                                <div class="na-spike ns5"></div>
                            </div>
                        </div>
                    </div>
                    <div class="na-headband"></div>
                    <div class="na-head" style="margin-top:20px">
                        <div class="na-brow L"></div>
                        <div class="na-brow R"></div>
                        <div class="na-eyes">
                            <div class="na-eye"></div>
                            <div class="na-eye"></div>
                        </div>
                        <div class="na-whisker wl1"></div>
                        <div class="na-whisker wl2"></div>
                        <div class="na-whisker wl3"></div>
                        <div class="na-whisker wr1"></div>
                        <div class="na-whisker wr2"></div>
                        <div class="na-whisker wr3"></div>
                        <div class="na-mouth"></div>
                    </div>
                    <div style="position:relative;margin-top:2px">
                        <div class="na-arm L">
                            <div class="na-hand L">
                                <div class="rasengan"></div>
                            </div>
                        </div>
                        <div class="na-arm R">
                            <div class="na-hand R"></div>
                        </div>
                        <div class="na-body"></div>
                    </div>
                    <div class="na-legs">
                        <div class="na-leg"></div>
                        <div class="na-leg"></div>
                    </div>
                </div>
            </div>

            <div class="sfx"
                style="bottom:37%;right:8px;font-size:36px;color:var(--na-orange);-webkit-text-stroke:2px var(--ink);transform:rotate(10deg);z-index:6;animation:pulse-sfx 2.5s ease-in-out infinite .5s">
                RAAAAH!!</div>
            <div class="sfx"
                style="bottom:37%;left:8px;font-size:16px;color:#fff;opacity:.4;transform:rotate(-6deg);z-index:6">
                螺旋丸!!</div>

            <div class="panel-num">p.2 — NARUTO</div>
        </div>

        <!-- ═══ BOTTOM BAR ═══ -->
        <div class="bottom-bar">
            <div class="bar-op">
                <div class="sec-label">— ⚓ ONE PIECE ROUTE —</div>
                <a href="/" class="manga-btn btn-op">⚓ SET SAIL HOME</a>
                <a href="javascript:history.back()" class="manga-btn btn-ghost">← RETREAT</a>
                <div class="page-note">Grand Line — Error Log vol.404<br>グランドライン — エラー記録</div>
            </div>

            <div class="bar-center">
                <!-- animated 404 -->
                <div class="vs-pill">ONE PIECE × NARUTO</div>
                <div class="giant-404">
                    <span class="g4-op">4</span><span class="g0">0</span><span class="g4-na">4</span>
                </div>
                <div class="center-sub">
                    The page you seek has been lost at sea...<br>
                    or sealed by a forbidden jutsu.
                </div>
                <div class="center-jp">ページが見つかりませんでした</div>
                <div class="page-note" style="margin-top:4px">ch.404 / vol.∞ / p.gone</div>
            </div>

            <div class="bar-na">
                <div class="sec-label">— 🍥 NARUTO PATH —</div>
                <a href="/" class="manga-btn btn-na">🍥 BELIEVE IT! GO HOME</a>
                <a href="/contact" class="manga-btn btn-ghost">✉ SEND A SCROLL</a>
                <div class="page-note">Hidden Leaf — 404番 木ノ葉隠れ<br>ページが見つかりません</div>
            </div>
        </div>

    </div><!-- /stage -->

    <script>
        const cur = document.getElementById('cur');
        const curRing = document.getElementById('curRing');
        const canvas = document.getElementById('speedCanvas');
        const ctx = canvas.getContext('2d');

        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight
        });

        let mx = window.innerWidth / 2,
            my = window.innerHeight / 2;
        let rx = mx,
            ry = my;

        document.addEventListener('mousemove', e => {
            mx = e.clientX;
            my = e.clientY;
            cur.style.left = mx + 'px';
            cur.style.top = my + 'px';
        });

        (function ringFollow() {
            rx += (mx - rx) * .12;
            ry += (my - ry) * .12;
            curRing.style.left = rx + 'px';
            curRing.style.top = ry + 'px';
            requestAnimationFrame(ringFollow);
        })();

        /* Stars for OP panel */
        const starsOp = document.getElementById('starsOp');
        for (let i = 0; i < 60; i++) {
            const s = document.createElement('div');
            s.className = 'star-op';
            const sz = Math.random() * 2.5 + .5;
            s.style.cssText =
                `width:${sz}px;height:${sz}px;top:${Math.random()*100}%;left:${Math.random()*100}%;--sd:${(Math.random()*2+1).toFixed(1)}s;animation-delay:${(Math.random()*2).toFixed(1)}s`;
            starsOp.appendChild(s);
        }

        /* Click: ink splatter + impact word + speed lines */
        const words = ['DOOOM!!', 'NANI?!', 'BAAM!!', 'CRASH!!', 'WHOOSH!!', 'CRACK!!', 'SLASH!!', 'BOOM!!'];
        document.addEventListener('click', e => {
            if (e.target.closest('a')) return;

            /* Ink dots */
            for (let i = 0; i < 12; i++) {
                const d = document.createElement('div');
                d.className = 'splatter';
                const sz = Math.random() * 28 + 6;
                const ang = Math.random() * Math.PI * 2;
                const dist = Math.random() * 80 + 20;
                d.style.cssText =
                    `width:${sz}px;height:${sz}px;left:${e.clientX+Math.cos(ang)*dist-sz/2}px;top:${e.clientY+Math.sin(ang)*dist-sz/2}px`;
                document.body.appendChild(d);
                setTimeout(() => d.remove(), 700);
            }

            /* Impact word */
            const imp = document.createElement('div');
            imp.className = 'impact';
            imp.textContent = words[Math.floor(Math.random() * words.length)];
            imp.style.left = e.clientX + 'px';
            imp.style.top = e.clientY + 'px';
            imp.style.color = Math.random() > .5 ? 'var(--op-red)' : 'var(--na-orange)';
            document.body.appendChild(imp);
            setTimeout(() => imp.remove(), 750);

            /* Speed lines flash */
            flashSpeedLines(e.clientX, e.clientY);
        });

        function flashSpeedLines(cx, cy) {
            canvas.classList.add('active');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = 'rgba(0,0,0,0.12)';
            for (let i = 0; i < 36; i++) {
                const ang = (i / 36) * Math.PI * 2;
                const len = Math.max(canvas.width, canvas.height) * 1.5;
                ctx.lineWidth = Math.random() * 3 + .5;
                ctx.beginPath();
                ctx.moveTo(cx, cy);
                ctx.lineTo(cx + Math.cos(ang) * len, cy + Math.sin(ang) * len);
                ctx.stroke();
            }
            setTimeout(() => {
                canvas.classList.remove('active');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }, 220);
        }

        /* Keyboard: press R to trigger rasengan easter egg */
        document.addEventListener('keydown', e => {
            if (e.key.toLowerCase() === 'r') {
                flashSpeedLines(window.innerWidth / 2, window.innerHeight / 2);
                const imp = document.createElement('div');
                imp.className = 'impact';
                imp.textContent = '螺旋丸!!';
                imp.style.left = '50vw';
                imp.style.top = '50vh';
                imp.style.fontSize = '52px';
                imp.style.color = 'var(--na-orange)';
                document.body.appendChild(imp);
                setTimeout(() => imp.remove(), 800);
            }
            if (e.key.toLowerCase() === 'g') {
                flashSpeedLines(window.innerWidth / 4, window.innerHeight / 2);
                const imp = document.createElement('div');
                imp.className = 'impact';
                imp.textContent = 'GOMU GOMU!!';
                imp.style.left = '25vw';
                imp.style.top = '50vh';
                imp.style.fontSize = '42px';
                imp.style.color = 'var(--op-red)';
                document.body.appendChild(imp);
                setTimeout(() => imp.remove(), 800);
            }
        });
    </script>
</body>

</html>
