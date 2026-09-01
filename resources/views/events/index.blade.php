<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events - ScyrolynX Ticketing</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-950 min-h-screen text-slate-100">

    <!-- Header -->
    <header class="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-blue-500 rounded-md flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <span class="text-white font-bold text-sm">S</span>
                </div>
                <span class="text-lg font-bold text-white">ScyrolynX</span>
            </div>
            <nav class="flex gap-6 text-sm text-slate-400">
                <a href="/events" class="text-violet-400 font-medium">Events</a>
                <a href="/my-tickets" class="hover:text-violet-400">My Tickets</a>
                <span class="cursor-not-allowed">About</span>
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-violet-900 via-slate-900 to-blue-900">
        <div class="absolute inset-0 opacity-30 bg-[radial-gradient(circle_at_20%_20%,_#8b5cf6_0%,_transparent_40%),radial-gradient(circle_at_80%_60%,_#3b82f6_0%,_transparent_40%)]"></div>
        <div class="relative max-w-6xl mx-auto px-6 py-20 text-center">
            <span class="inline-block bg-violet-500/10 border border-violet-400/30 text-violet-300 text-xs font-semibold px-4 py-1.5 rounded-full mb-5 uppercase tracking-wide">
                Powered by ScyrolynX
            </span>
            <h1 class="text-5xl font-bold mb-4 bg-gradient-to-r from-violet-300 via-blue-300 to-violet-300 bg-clip-text text-transparent">
                Discover Upcoming Events
            </h1>
            <p class="text-slate-300 text-lg max-w-xl mx-auto">
                Book tickets to the hottest concerts, meetups, and experiences — all in one place.
            </p>

            <div class="flex justify-center gap-10 mt-10">
                <div>
                    <div class="text-3xl font-bold text-white">{{ $events->count() }}</div>
                    <div class="text-sm text-slate-400">Live Events</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">{{ $events->sum(fn($e) => $e->ticketTypes->count()) }}</div>
                    <div class="text-sm text-slate-400">Ticket Types</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">100%</div>
                    <div class="text-sm text-slate-400">Secure Checkout</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Grid -->
    <main class="max-w-6xl mx-auto px-6 py-16">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-white">Upcoming Events</h2>
            <span class="text-sm text-slate-400">{{ $events->count() }} events found</span>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($events as $event)
                <div class="group bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden hover:border-violet-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-violet-500/10">

                    <div class="h-32 bg-gradient-to-br from-violet-600 via-purple-600 to-blue-600 relative flex items-end p-4">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.15),_transparent_60%)]"></div>
                        <span class="relative bg-black/30 backdrop-blur text-white text-xs font-semibold px-3 py-1 rounded-full">
                            {{ $event->event_date->format('M j, Y') }}
                        </span>
                    </div>

                    <div class="p-6">
                        <h3 class="text-xl font-bold text-white mb-1 group-hover:text-violet-300 transition-colors">
                            {{ $event->title }}
                        </h3>

                        <div class="flex items-center gap-1.5 text-sm text-slate-400 mb-4">
                            <span>📍 {{ $event->venue }}</span>
                            <span class="text-slate-600">·</span>
                            <span>🕐 {{ $event->event_date->format('g:i A') }}</span>
                        </div>

                        @if($event->description)
                            <p class="text-slate-400 text-sm mb-5 leading-relaxed overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $event->description }}</p>
                        @endif

                        <div class="border-t border-slate-800 pt-4 mb-5">
                            <div class="space-y-2">
                                @foreach ($event->ticketTypes as $ticketType)
                                    <div class="flex justify-between items-center bg-slate-800/50 rounded-lg px-4 py-2.5">
                                        <span class="text-sm font-medium text-slate-300">{{ $ticketType->name }}</span>
                                        <span class="text-sm font-bold text-violet-300">GHS {{ number_format($ticketType->price, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <a href="/events/{{ $event->id }}" class="block text-center bg-gradient-to-r from-violet-600 to-blue-600 hover:from-violet-500 hover:to-blue-500 text-white text-sm font-semibold py-2.5 rounded-lg transition-all duration-200 shadow-lg shadow-violet-900/50">
                            View Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 mt-12">
        <div class="max-w-6xl mx-auto px-6 py-8 flex items-center justify-between text-sm text-slate-500">
            <span>© {{ date('Y') }} ScyrolynX. All rights reserved.</span>
            <span>Built with Laravel & Tailwind CSS</span>
        </div>
    </footer>
</body>
</html>
