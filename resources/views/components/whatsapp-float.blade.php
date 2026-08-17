@php
    $whatsappNumber = '213556988175';
    $whatsappUrl = 'https://wa.me/'.$whatsappNumber;
@endphp

<a href="{{ $whatsappUrl }}"
   target="_blank"
   rel="noopener noreferrer"
   class="wa-float"
   aria-label="{{ __('messages.support_24_7') }} — WhatsApp +213 556 98 81 75">
    <span class="wa-float__pulse" aria-hidden="true"></span>
    <span class="wa-float__pulse wa-float__pulse--delayed" aria-hidden="true"></span>
    <img src="{{ asset('images/contact-us.png') }}"
         alt="{{ __('messages.support_24_7') }}"
         class="wa-float__img"
         width="500"
         height="500"
         decoding="async">
</a>

<style>
    /* Floating WhatsApp support button. Kept as plain CSS in the component so it
       ships without a Vite rebuild, and uses physical `right` so it stays on the
       bottom-right in both LTR and RTL. */
    .wa-float {
        position: fixed;
        right: 14px;
        /* Clears the mobile bottom navigation and the sticky Buy Now bar. */
        bottom: calc(78px + env(safe-area-inset-bottom, 0px));
        z-index: 9990;
        display: block;
        width: 78px;
        height: 78px;
        line-height: 0;
        -webkit-tap-highlight-color: transparent;
    }

    .wa-float__img {
        position: relative;
        z-index: 2;
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: drop-shadow(0 6px 14px rgba(0, 0, 0, 0.45));
        animation: wa-float-breathe 2.6s ease-in-out infinite;
        transition: transform 0.2s ease-out;
    }

    /* Expanding rings behind the sticker. */
    .wa-float__pulse {
        position: absolute;
        top: 50%;
        left: 50%;
        z-index: 1;
        width: 62%;
        height: 62%;
        margin: -31% 0 0 -31%;
        border-radius: 9999px;
        background-color: rgba(37, 211, 102, 0.55);
        animation: wa-float-pulse 2.2s cubic-bezier(0.22, 0.61, 0.36, 1) infinite;
        pointer-events: none;
    }

    .wa-float__pulse--delayed {
        animation-delay: 1.1s;
    }

    .wa-float:hover .wa-float__img,
    .wa-float:focus-visible .wa-float__img {
        transform: scale(1.07);
    }

    .wa-float:active .wa-float__img {
        transform: scale(0.96);
    }

    .wa-float:focus-visible {
        outline: 2px solid #25d366;
        outline-offset: 4px;
        border-radius: 12px;
    }

    @keyframes wa-float-pulse {
        0% {
            transform: scale(0.75);
            opacity: 0.75;
        }
        70% {
            transform: scale(1.75);
            opacity: 0;
        }
        100% {
            transform: scale(1.75);
            opacity: 0;
        }
    }

    @keyframes wa-float-breathe {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.06);
        }
    }

    @media (min-width: 768px) {
        .wa-float {
            right: 24px;
            bottom: 24px;
            width: 104px;
            height: 104px;
        }
    }

    @media print {
        .wa-float {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .wa-float__img {
            animation: none;
        }

        .wa-float__pulse {
            animation: none;
            opacity: 0.35;
            transform: scale(1.2);
        }
    }
</style>
