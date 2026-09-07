<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events - ScyrolynX Ticketing</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-950 min-h-screen text-slate-100">

    <header class="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-blue-500 rounded-md flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <span class="text-white font-bold text-sm">S</span>
                </div>
                <span class="text-lg font-bold text-white">ScyrolynX</span>
            </div>

            <div class="flex items-center gap-4">
                <nav class="flex gap-2 text-sm">
                    <a href="/events" class="nav-pill px-4 py-2 rounded-full bg-violet-600 text-white font-medium transition-all duration-300">Events</a>
                    <a href="/my-tickets" class="nav-pill px-4 py-2 rounded-full bg-slate-800 text-slate-200 font-medium hover:bg-slate-700 transition-all duration-300">My Tickets</a>
                    <a href="#about" class="nav-pill px-4 py-2 rounded-full bg-slate-800 text-slate-200 font-medium hover:bg-slate-700 transition-all duration-300">About</a>
                </nav>

                <div class="w-px h-6 bg-slate-800"></div>

                <button id="logout-btn" class="hidden px-4 py-2 rounded-full bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20 text-sm font-medium transition-all duration-300">Logout</button>
                <a id="login-link" href="/login" class="px-4 py-2 rounded-full bg-slate-800 text-slate-200 text-sm font-medium hover:bg-slate-700 transition-all duration-300">Login</a>
            </div>
        </div>
    </header>

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
        </div>
    </section>

    <main class="max-w-6xl mx-auto px-6 py-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Upcoming Events</h2>
            <span id="event-count" class="text-sm text-slate-400">Loading...</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-8 grid grid-cols-2 md:grid-cols-4 gap-3">
            <select id="filter-category" class="bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <option value="">All categories</option>
                <option value="concert">Concert</option>
                <option value="conference">Conference</option>
                <option value="sports">Sports</option>
                <option value="other">Other</option>
            </select>
            <input id="filter-date-from" type="date" class="bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
            <input id="filter-date-to" type="date" class="bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
            <input id="filter-max-price" type="number" placeholder="Max price (GHS)" class="bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
        </div>

        <div id="events-grid" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"></div>
        <p id="no-events" class="hidden text-slate-400 text-center py-12">No events match these filters.</p>
    </main>

    <footer class="border-t border-slate-800 mt-12">
        <div class="max-w-6xl mx-auto px-6 py-8 flex items-center justify-between text-sm text-slate-500">
            <span>© {{ date('Y') }} ScyrolynX. All rights reserved.</span>
            <span>Built with Laravel & Tailwind CSS</span>
        </div>
    </footer>

    <script>
        const loginLink = document.getElementById('login-link');
        const logoutBtn = document.getElementById('logout-btn');
        const token = localStorage.getItem('token');

        if (token) {
            loginLink.classList.add('hidden');
            logoutBtn.classList.remove('hidden');
        }

        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem('token');
            window.location.href = '/events';
        });

        document.querySelectorAll('.nav-pill').forEach(pill => {
            pill.addEventListener('click', (e) => {
                const href = pill.getAttribute('href');
                if (href.startsWith('#')) return;
                e.preventDefault();
                pill.classList.add('shadow-lg', 'shadow-violet-500/60', 'ring-2', 'ring-violet-400');
                setTimeout(() => { window.location.href = href; }, 150);
            });
        });

        function renderEvents(events) {
            const grid = document.getElementById('events-grid');
            const noEvents = document.getElementById('no-events');
            grid.innerHTML = '';

            document.getElementById('event-count').textContent = events.length + ' events found';

            if (events.length === 0) {
                noEvents.classList.remove('hidden');
                return;
            }
            noEvents.classList.add('hidden');

            events.forEach(event => {
                const ticketRows = event.ticket_types.map(tt => `
                    <div class="flex justify-between items-center bg-slate-800/50 rounded-lg px-4 py-2.5">
                        <span class="text-sm font-medium text-slate-300">${tt.name}</span>
                        <span class="text-sm font-bold text-violet-300">GHS ${Number(tt.price).toFixed(2)}</span>
                    </div>
                `).join('');

                const card = document.createElement('div');
                card.className = 'group bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden hover:border-violet-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-violet-500/10';
                card.innerHTML = `
                    <div class="h-32 bg-gradient-to-br from-violet-600 via-purple-600 to-blue-600 relative flex items-end p-4">
                        <span class="relative bg-black/30 backdrop-blur text-white text-xs font-semibold px-3 py-1 rounded-full">
                            ${new Date(event.event_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="text-xs uppercase tracking-wide text-violet-400 mb-1">${event.category}</div>
                        <h3 class="text-xl font-bold text-white mb-1 group-hover:text-violet-300 transition-colors">${event.title}</h3>
                        <div class="flex items-center gap-1.5 text-sm text-slate-400 mb-4">
                            <span>📍 ${event.venue}</span>
                        </div>
                        <div class="border-t border-slate-800 pt-4 mb-5">
                            <div class="space-y-2">${ticketRows}</div>
                        </div>
                        <a href="/events/${event.id}" class="block text-center bg-gradient-to-r from-violet-600 to-blue-600 hover:from-violet-500 hover:to-blue-500 text-white text-sm font-semibold py-2.5 rounded-lg transition-all duration-200 shadow-lg shadow-violet-900/50">
                            View Details
                        </a>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        async function loadEvents() {
            const params = new URLSearchParams();
            const category = document.getElementById('filter-category').value;
            const dateFrom = document.getElementById('filter-date-from').value;
            const dateTo = document.getElementById('filter-date-to').value;
            const maxPrice = document.getElementById('filter-max-price').value;

            if (category) params.set('category', category);
            if (dateFrom) params.set('date_from', dateFrom);
            if (dateTo) params.set('date_to', dateTo);
            if (maxPrice) params.set('max_price', maxPrice);

            const res = await fetch('/api/v1/events?' + params.toString());
            const data = await res.json();
            renderEvents(data.events);
        }

        ['filter-category', 'filter-date-from', 'filter-date-to', 'filter-max-price'].forEach(id => {
            document.getElementById(id).addEventListener('change', loadEvents);
        });

        loadEvents();
    </script>
</body>
</html>
