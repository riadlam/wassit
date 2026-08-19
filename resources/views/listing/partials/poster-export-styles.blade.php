*, *::before, *::after { box-sizing: border-box; }
.listing-poster {
    position: relative;
    width: 681px;
    height: 1024px;
    margin: 0 auto;
    overflow: hidden;
    font-family: Inter, ui-sans-serif, system-ui, "Segoe UI", sans-serif;
    background-color: #c80000;
}
.lp-bg {
    position: absolute;
    inset: 0;
    width: 681px;
    height: 1024px;
    object-fit: fill;
    z-index: 0;
    pointer-events: none;
}
.listing-poster > :not(.lp-bg) { z-index: 1; }
.lp-featured {
    position: absolute;
    left: 14px;
    top: 200px;
    width: 248px;
    height: 286px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.lp-primary {
    position: absolute;
    left: 272px;
    top: 200px;
    width: 395px;
    height: 286px;
    overflow: hidden;
    border-radius: 12px;
    border: 3px solid #fff;
    background: #111;
    box-shadow: 0 3px 0 rgba(0,0,0,0.25);
}
.lp-frame-viewport {
    position: absolute;
    inset: 0;
    overflow: hidden;
}
.lp-primary .lp-frame-viewport { border-radius: 9px; }
.lp-primary-watermark {
    position: absolute;
    left: 50%;
    top: 52%;
    z-index: 3;
    pointer-events: none;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 0.12em;
    color: rgba(255, 255, 255, 0.22);
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.28);
    white-space: nowrap;
}
.lp-collection-badge {
    position: absolute;
    right: 8px;
    top: 8px;
    z-index: 8;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 58px;
    height: 58px;
}
.lp-collection-badge-icon {
    width: 54px;
    height: 54px;
    object-fit: contain;
    display: block;
    filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.55));
}
.lp-frame-viewport img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    display: block;
    transform-origin: center center;
}
.lp-skin-tags {
    position: absolute;
    top: 5px;
    right: 6px;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
    max-width: 70%;
    z-index: 2;
}
.lp-tag-img {
    height: 18px;
    width: auto;
    max-width: 100%;
    object-fit: contain;
    display: block;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.65));
}
.lp-tag-painted {
    font-size: 6px;
    font-weight: 900;
    padding: 2px 5px;
    background: rgba(190, 24, 93, 0.9);
    color: #fff;
    border-radius: 999px;
    text-transform: uppercase;
}
.lp-effects {
    position: absolute;
    left: 14px;
    top: 494px;
    width: 248px;
    height: 128px;
    background: rgba(10,10,10,0.88);
    border: 2px solid #fff;
    border-radius: 10px;
    padding: 6px 7px 7px;
}
.lp-effects-title {
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 0.14em;
    color: #fff;
    text-align: center;
    margin: 0 0 5px;
}
.lp-effects-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: repeat(2, 1fr);
    gap: 4px;
    height: calc(100% - 20px);
}
.lp-effect {
    background: #0f0f0f;
    border-radius: 6px;
    overflow: hidden;
}
.lp-effect img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}
.lp-stats {
    position: absolute;
    left: 272px;
    top: 494px;
    width: 395px;
    height: 64px;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    align-items: center;
    background: rgba(18,18,18,0.92);
    border: 2px solid #fff;
    border-radius: 10px;
    color: #fff;
    padding: 4px;
}
.lp-stat {
    text-align: center;
    border-right: 1px solid #3f3f46;
    padding: 0 2px;
}
.lp-stat:last-child { border-right: 0; }
.lp-stat-val {
    display: block;
    font-weight: 900;
    font-size: 15px;
    line-height: 1.1;
}
.lp-stat-lbl {
    display: block;
    font-size: 7px;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: #d4d4d8;
    margin-top: 2px;
}
.lp-recalls {
    position: absolute;
    left: 272px;
    top: 564px;
    width: 395px;
    height: 70px;
    z-index: 4;
}
.lp-recalls-title { display: none; }
.lp-recalls-row {
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    gap: 4px;
    height: 100%;
}
.lp-recall {
    flex: 1 1 0;
    min-width: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.lp-recall img {
    width: auto;
    height: 100%;
    max-width: 100%;
    object-fit: contain;
    display: block;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.45));
}
.lp-gallery {
    position: absolute;
    left: 14px;
    top: 638px;
    width: 653px;
    height: 182px;
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 6px;
}
.lp-skin {
    position: relative;
    background: #111;
    border-radius: 8px;
    overflow: hidden;
    min-height: 0;
    border: 2px solid #fff;
}
.lp-rarity {
    font-size: 7px;
    font-weight: 900;
    padding: 2px 6px;
    background: #111;
    color: #fff;
    border: 1px solid #fbbf24;
    text-transform: uppercase;
    white-space: nowrap;
}
.lp-rarity.is-prime, .lp-rarity.is-legend { border-color: #fbbf24; color: #fde68a; }
.lp-rarity.is-collector { border-color: #c084fc; color: #e9d5ff; }
.lp-rarity.is-special, .lp-rarity.is-star { border-color: #67e8f9; color: #a5f3fc; }
.lp-rarity.is-elite { border-color: #93c5fd; color: #dbeafe; }
.lp-skin-meta {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 4px 5px 5px;
    background: rgba(8, 18, 40, 0.92);
    z-index: 2;
}
.lp-skin-name {
    font-size: 9px;
    font-weight: 800;
    color: #5eead4;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.15;
    margin: 0;
}
.lp-hero-name {
    font-size: 8px;
    font-weight: 700;
    color: #93c5fd;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0;
}
.lp-price-slot {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    line-height: 1;
    z-index: 10;
    overflow: visible;
}
.lp-price-value {
    display: inline-block;
    font-family: "Bebas Neue", "Montserrat", Impact, sans-serif;
    font-weight: 400;
    letter-spacing: 0.04em;
    line-height: 1;
    transform-origin: center center;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.95), 0 2px 0 rgba(153, 27, 27, 0.18), 0 4px 8px rgba(127, 29, 29, 0.28);
}
.listing-poster.is-basic .lp-featured { display: none; }
.listing-poster.is-basic .lp-collection-badge { right: 10px; top: 10px; width: 64px; height: 64px; }
.listing-poster.is-basic .lp-collection-badge-icon { width: 60px; height: 60px; }
.listing-poster.is-basic .lp-effects { left: 10px; top: 10px; width: 248px; height: 128px; }
.listing-poster.is-basic .lp-recalls {
    left: 10px; top: 144px; width: 248px; height: 128px;
    background: rgba(10,10,10,0.88); border: 2px solid #fff; border-radius: 10px; padding: 6px 7px 7px;
}
.listing-poster.is-basic .lp-recalls-title {
    display: block; font-size: 10px; font-weight: 900; letter-spacing: 0.14em; color: #fff; text-align: center; margin: 0 0 5px;
}
.listing-poster.is-basic .lp-recalls-row {
    display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(2, 1fr); gap: 4px; height: calc(100% - 20px);
}
.listing-poster.is-basic .lp-recall { min-height: 0; height: 100%; background: #0f0f0f; border-radius: 6px; }
.listing-poster.is-basic .lp-recall img { width: 100%; height: 100%; max-width: none; object-fit: contain; filter: none; }
.listing-poster.is-basic .lp-primary { left: 266px; top: 10px; width: 405px; height: 262px; }
.listing-poster.is-basic .lp-stats { left: 10px; top: 280px; width: 661px; height: 54px; overflow: hidden; padding: 4px 6px; }
.listing-poster.is-basic .lp-stat-val { font-size: 13px; }
.listing-poster.is-basic .lp-stat-lbl { font-size: 6px; letter-spacing: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.listing-poster.is-basic .lp-gallery {
    left: 10px; top: 342px; width: 661px; height: 568px;
    display: flex; flex-wrap: wrap; align-content: stretch; gap: 4px;
}
.listing-poster.is-basic .lp-skin.is-large-tile .lp-skin-name { font-size: 11px; }
.listing-poster.is-basic .lp-skin.is-large-tile .lp-hero-name { font-size: 9px; }
.listing-poster.is-basic .lp-skin.is-large-tile .lp-tag-img { height: 16px; }
.listing-poster.is-basic .lp-skin.is-medium-tile .lp-skin-name { font-size: 8px; }
.listing-poster.is-basic .lp-skin.is-medium-tile .lp-hero-name { font-size: 7px; }
.listing-poster.is-basic .lp-skin { border-width: 1.5px; border-radius: 6px; }
.listing-poster.is-basic .lp-skin-meta { padding: 3px; }
.listing-poster.is-basic .lp-skin-name { font-size: 7px; }
.listing-poster.is-basic .lp-hero-name { font-size: 6px; }
.listing-poster.is-basic .lp-tag-img { height: 12px; }
