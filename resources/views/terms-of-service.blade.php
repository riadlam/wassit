@extends('layouts.app', ['title' => __('terms.title') . ' | Wassit'])

@section('content')
    @php
        $sections = __('terms.sections');
    @endphp

    <div class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-b from-[#0f111b] via-[#151721] to-[#1b1a1e]"></div>
            <div class="absolute top-0 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-red-500/10 blur-3xl"></div>
        </div>

        <div class="relative z-10 px-4 py-16 mx-auto max-w-4xl sm:px-6 sm:py-24 lg:px-8">
            <header class="mb-10 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 mb-5 text-xl text-red-400 border rounded-2xl border-red-400/20 bg-red-400/10">
                    <i class="fa-solid fa-file-contract" aria-hidden="true"></i>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-5xl">
                    {{ __('terms.title') }}
                </h1>
                <p class="mt-4 text-sm text-gray-400">{{ __('terms.effective_date') }}</p>
            </header>

            <div class="p-6 border shadow-2xl rounded-2xl border-white/10 bg-[#11131b]/90 sm:p-10">
                <p class="text-base leading-8 text-gray-300 sm:text-lg">
                    {{ __('terms.intro') }}
                </p>

                <div class="mt-10 space-y-10">
                    @foreach($sections as $section)
                        <section>
                            <h2 class="mb-4 text-xl font-semibold text-white sm:text-2xl">
                                {{ $section['title'] }}
                            </h2>

                            @foreach($section['paragraphs'] ?? [] as $paragraph)
                                <p class="mt-3 leading-7 text-gray-300">{{ $paragraph }}</p>
                            @endforeach

                            @if(!empty($section['items']))
                                <ul class="mt-4 space-y-3 text-gray-300 list-disc ps-6 marker:text-red-400">
                                    @foreach($section['items'] as $item)
                                        <li class="leading-7">{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if($loop->last)
                                <a
                                    href="https://wa.me/213556988175"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 px-5 py-3 mt-5 font-medium text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-[#11131b]"
                                >
                                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                                    {{ __('terms.contact_label') }}
                                </a>
                            @endif
                        </section>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
