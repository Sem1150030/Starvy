<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Starvy') }} — learning that feels like play</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @vite(['resources/css/app.css'])
        @fluxAppearance
    </head>
    <body class="ocean-bg min-h-screen font-sans antialiased">
        @php
            // Showcase subjects for the landing page. Tokens come from the Ocean
            // subject palette (see CLAUDE.md / starvy-styling). Progress numbers are
            // still mocked — wire to a Livewire component once student data exists.
            $subjects = [
                ['name' => 'Math',    'emoji' => '🔢', 'lessons' => 24, 'progress' => 60, 'bg' => '#DDF5EC', 'accent' => '#2DD4A8', 'title' => '#0A5A45', 'label' => '#0F7A5E'],
                ['name' => 'Reading', 'emoji' => '📚', 'lessons' => 18, 'progress' => 35, 'bg' => '#D6F3F1', 'accent' => '#14B8B0', 'title' => '#0A5751', 'label' => '#0E7A72'],
                ['name' => 'Science', 'emoji' => '🔬', 'lessons' => 21, 'progress' => 80, 'bg' => '#DAF1FB', 'accent' => '#29B6E8', 'title' => '#0B4F6B', 'label' => '#126C90'],
                ['name' => 'Art',     'emoji' => '🎨', 'lessons' => 12, 'progress' => 20, 'bg' => '#DCEAFE', 'accent' => '#3B82F6', 'title' => '#163E8A', 'label' => '#1E56B0'],
                ['name' => 'Music',   'emoji' => '🎵', 'lessons' => 15, 'progress' => 50, 'bg' => '#E4E6FB', 'accent' => '#6366F1', 'title' => '#2E2E8A', 'label' => '#3E3EB0'],
            ];
        @endphp

        <div class="mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 py-6 lg:px-8">
            {{-- Top bar --}}
            <header class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="display flex items-center gap-2 text-2xl" style="color: var(--color-ink)">
                    <span aria-hidden="true">🌟</span>
                    Starvy
                </a>

                <nav class="flex items-center gap-2">
                    @auth
                        <flux:button href="{{ route('dashboard') }}" variant="primary" class="!rounded-full !px-7 display">
                            Go to my dashboard
                        </flux:button>
                    @else
                        <flux:button href="{{ route('login') }}" variant="ghost" class="!rounded-full display">
                            Log in
                        </flux:button>
                        @if (Route::has('register'))
                            <flux:button href="{{ route('register') }}" variant="primary" class="!rounded-full !px-7 display">
                                Join Starvy
                            </flux:button>
                        @endif
                    @endauth
                </nav>
            </header>

            {{-- Hero --}}
            <main class="flex flex-1 items-center py-10 lg:py-16">
                <div class="grid w-full items-center gap-10 lg:grid-cols-2">
                    <div>
                        <span class="display inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm shadow-sm" style="color: var(--color-ink)">
                            <span aria-hidden="true">✨</span> Learning that feels like play
                        </span>

                        <h1 class="display mt-5 text-5xl leading-tight lg:text-6xl" style="color: var(--color-ink)">
                            Hi there! Ready for<br>your next adventure?
                        </h1>

                        <p class="mt-5 max-w-md text-lg" style="color: var(--color-ink)">
                            Pick a subject, play through fun lessons, and collect stars along
                            the way. Your friendly study buddy is waiting!
                        </p>

                        {{-- Reward loop: warm counters that pop against the cool palette --}}
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <span class="display inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-4 py-2 text-amber-800">
                                <span aria-hidden="true">⭐</span> 128 stars
                            </span>
                            <span class="display inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-4 py-2 text-orange-800">
                                <span aria-hidden="true">🔥</span> 5 day streak
                            </span>
                            <span class="display inline-flex items-center gap-1.5 rounded-full bg-white px-4 py-2 shadow-sm" style="color: var(--color-ink)">
                                <span aria-hidden="true">🏆</span> 3 trophies
                            </span>
                        </div>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <flux:button href="{{ route('register') }}" variant="primary" class="!rounded-full !px-8 !py-3 display !text-base">
                                Start learning
                            </flux:button>
                            <flux:button href="{{ route('login') }}" variant="subtle" class="!rounded-full !px-7 display">
                                I already have an account
                            </flux:button>
                        </div>
                    </div>

                    {{-- Mascot --}}
                    <div class="flex justify-center lg:justify-end">
                        <div class="hero-gradient flex h-72 w-72 items-center justify-center rounded-[2.5rem] shadow-sm lg:h-80 lg:w-80">
                            <span class="float text-[8rem] leading-none" role="img" aria-label="Friendly study buddy">🐳</span>
                        </div>
                    </div>
                </div>
            </main>

            {{-- Subjects --}}
            <section class="pb-12">
                <h2 class="display text-2xl" style="color: var(--color-ink)">
                    Pick a subject to explore
                </h2>
                <p class="mt-1" style="color: var(--color-ink)">There's something fun for everyone.</p>

                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($subjects as $subject)
                        <a href="{{ route('register') }}"
                           class="pop block rounded-3xl p-5 shadow-sm"
                           style="background: {{ $subject['bg'] }}">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl text-2xl"
                                 style="background: {{ $subject['accent'] }}">
                                <span aria-hidden="true">{{ $subject['emoji'] }}</span>
                            </div>
                            <h3 class="display mt-4 text-lg" style="color: {{ $subject['title'] }}">
                                {{ $subject['name'] }}
                            </h3>
                            <p class="text-sm font-semibold" style="color: {{ $subject['label'] }}">
                                {{ $subject['lessons'] }} lessons
                            </p>
                            <div class="mt-4 h-2.5 rounded-full bg-black/10">
                                <div class="h-full rounded-full"
                                     style="width: {{ $subject['progress'] }}%; background: {{ $subject['accent'] }}"></div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        @fluxScripts
    </body>
</html>
