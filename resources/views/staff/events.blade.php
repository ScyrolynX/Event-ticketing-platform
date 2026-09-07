<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Events - ScyrolynX Staff</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-950 min-h-screen text-slate-100">

    <header class="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
            <span class="text-lg font-bold text-white">ScyrolynX Staff</span>
            <a href="/staff" class="text-sm text-slate-400 hover:text-violet-400">← Dashboard</a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-12">
        <h1 class="text-2xl font-bold mb-8">Manage Events</h1>

        <p id="error" class="hidden text-red-400 text-sm mb-4"></p>
        <p id="success" class="hidden text-green-400 text-sm mb-4"></p>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-10">
            <h2 class="font-semibold text-white mb-4">Create Event</h2>
            <form id="event-form" class="space-y-3">
                <input name="title" placeholder="Event title" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <select name="category" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                    <option value="">Select category...</option>
                    <option value="concert">Concert</option>
                    <option value="conference">Conference</option>
                    <option value="sports">Sports</option>
                    <option value="other">Other</option>
                </select>
                <select name="promoter_id" id="promoter-select" class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                    <option value="">No promoter (in-house event)</option>
                </select>
                <textarea name="description" placeholder="Description (optional)" class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm"></textarea>
                <input name="venue" placeholder="Venue" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <input name="event_date" type="datetime-local" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <button type="submit" class="bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-5 py-2 rounded-full transition-all duration-300 active:scale-95">Create Event</button>
            </form>
        </div>

        <div id="events-list" class="space-y-4"></div>
    </main>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        const errorEl = document.getElementById('error');
        const successEl = document.getElementById('success');

        async function loadPromotersIntoSelect() {
            const res = await fetch('/api/v1/manage/promoters', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            });
            if (!res.ok) return;
            const data = await res.json();
            const select = document.getElementById('promoter-select');
            data.promoters.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name + ' (' + p.commission_rate + '% commission)';
                select.appendChild(opt);
            });
        }

        async function loadEvents() {
            const res = await fetch('/api/v1/manage/events', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            });

            if (res.status === 403) {
                errorEl.textContent = 'You do not have permission to manage events.';
                errorEl.classList.remove('hidden');
                return;
            }

            const data = await res.json();
            const list = document.getElementById('events-list');
            list.innerHTML = '';

            data.events.forEach(event => {
                const card = document.createElement('div');
                card.className = 'bg-slate-900 border border-slate-800 rounded-xl p-5';
                card.innerHTML = `
                    <div class="font-semibold text-white mb-1">${event.title}</div>
                    <div class="text-xs text-slate-500 mb-3">${event.venue} — ${new Date(event.event_date).toLocaleString()}</div>
                    <div class="space-y-2 mb-3" id="tt-${event.id}">
                        ${event.ticket_types.map(tt => `
                            <div class="flex justify-between text-sm bg-slate-800/50 rounded px-3 py-2">
                                <span>${tt.name}</span>
                                <span>GHS ${tt.price} — ${tt.quantity_available} available</span>
                            </div>
                        `).join('')}
                    </div>
                    <form class="tt-form flex gap-2" data-event-id="${event.id}">
                        <input name="name" placeholder="Ticket type name" required class="flex-1 bg-slate-800 border border-slate-700 rounded-md px-2 py-1 text-xs">
                        <input name="price" type="number" step="0.01" placeholder="Price" required class="w-20 bg-slate-800 border border-slate-700 rounded-md px-2 py-1 text-xs">
                        <input name="quantity_available" type="number" placeholder="Qty" required class="w-20 bg-slate-800 border border-slate-700 rounded-md px-2 py-1 text-xs">
                        <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-xs px-3 py-1 rounded-md">Add</button>
                    </form>
                `;
                list.appendChild(card);
            });

            document.querySelectorAll('.tt-form').forEach(form => {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const eventId = form.dataset.eventId;
                    const payload = Object.fromEntries(new FormData(form).entries());

                    const res = await fetch(`/api/v1/manage/events/${eventId}/ticket-types`, {
                        method: 'POST',
                        headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
                        body: JSON.stringify(payload),
                    });

                    if (res.ok) loadEvents();
                });
            });
        }

        document.getElementById('event-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');

            const payload = Object.fromEntries(new FormData(e.target).entries());
            if (!payload.promoter_id) delete payload.promoter_id;

            const res = await fetch('/api/v1/manage/events', {
                method: 'POST',
                headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (!res.ok) {
                errorEl.textContent = data.message || 'Failed to create event.';
                errorEl.classList.remove('hidden');
                return;
            }

            successEl.textContent = 'Event created!';
            successEl.classList.remove('hidden');
            e.target.reset();
            loadEvents();
        });

        loadPromotersIntoSelect();
        loadEvents();
    </script>
</body>
</html>
