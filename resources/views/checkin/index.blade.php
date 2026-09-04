<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check-In - ScyrolynX Staff</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-950 min-h-screen text-slate-100">

    <header class="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
        <div class="max-w-2xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-blue-500 rounded-md flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <span class="text-white font-bold text-sm">S</span>
                </div>
                <span class="text-lg font-bold text-white">ScyrolynX Staff</span>
            </div>
            <a href="/events" class="text-sm text-slate-400 hover:text-violet-400">← Exit</a>
        </div>
    </header>

    <main class="max-w-md mx-auto px-6 py-16 text-center">
        <h1 class="text-2xl font-bold mb-2">Ticket Check-In</h1>
        <p class="text-slate-400 text-sm mb-10">Paste or scan a ticket's code below to admit it.</p>

        <div id="result" class="hidden rounded-2xl p-8 mb-6 border"></div>

        <form id="checkin-form" class="space-y-4">
            <textarea
                id="code-input"
                rows="3"
                placeholder="Ticket code..."
                class="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-slate-100 text-xs focus:outline-none focus:border-violet-500"
                required></textarea>
            <button type="submit"
                class="w-full bg-violet-600 hover:bg-violet-500 text-white font-medium py-3 rounded-full transition-all duration-300 active:scale-95">
                Check In
            </button>
        </form>
    </main>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        const resultEl = document.getElementById('result');

        document.getElementById('checkin-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const code = document.getElementById('code-input').value.trim();

            const res = await fetch('/api/v1/check-in', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ unique_code: code }),
            });

            const data = await res.json();
            resultEl.classList.remove('hidden');

            if (res.status === 403) {
                resultEl.className = 'rounded-2xl p-8 mb-6 border bg-red-500/10 border-red-500/40 text-red-300';
                resultEl.innerHTML = '<div class="text-4xl mb-2">🚫</div><div class="font-semibold">Not authorized</div><div class="text-sm mt-1">Your account does not have check-in access.</div>';
            } else if (res.ok) {
                resultEl.className = 'rounded-2xl p-8 mb-6 border bg-green-500/10 border-green-500/40 text-green-300';
                resultEl.innerHTML = '<div class="text-4xl mb-2">✅</div><div class="font-semibold">Ticket accepted</div><div class="text-sm mt-1">Ticket #' + data.ticket_id + ', admit guest.</div>';
            } else {
                resultEl.className = 'rounded-2xl p-8 mb-6 border bg-amber-500/10 border-amber-500/40 text-amber-300';
                resultEl.innerHTML = '<div class="text-4xl mb-2">⚠️</div><div class="font-semibold">' + (data.message || 'Rejected') + '</div>';
            }

            document.getElementById('code-input').value = '';
        });
    </script>
</body>
</html>
