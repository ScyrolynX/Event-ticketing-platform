<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Dashboard - ScyrolynX</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-950 min-h-screen text-slate-100">

    <header class="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
            <span class="text-lg font-bold text-white">ScyrolynX Staff</span>
            <a href="/staff" class="text-sm text-slate-400 hover:text-violet-400">← Dashboard</a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-12">
        <h1 class="text-2xl font-bold mb-8">Sales Dashboard</h1>

        <div class="grid grid-cols-2 gap-4 mb-10">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <div class="text-3xl font-bold text-white" id="total-revenue">—</div>
                <div class="text-sm text-slate-400">Total Revenue (GHS)</div>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
                <div class="text-3xl font-bold text-white" id="total-tickets">—</div>
                <div class="text-sm text-slate-400">Tickets Sold</div>
            </div>
        </div>

        <h2 class="font-semibold text-white mb-4">By Event</h2>
        <div id="events-list" class="space-y-3"></div>
    </main>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        (async () => {
            const res = await fetch('/api/v1/manage/dashboard', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            });

            if (res.status === 403) {
                document.body.innerHTML = '<div class="max-w-4xl mx-auto px-6 py-12 text-red-400">Only Admins can view the sales dashboard.</div>';
                return;
            }

            const data = await res.json();

            document.getElementById('total-revenue').textContent = Number(data.total_revenue).toFixed(2);
            document.getElementById('total-tickets').textContent = data.total_tickets_sold;

            const list = document.getElementById('events-list');
            data.events.forEach(e => {
                const row = document.createElement('div');
                row.className = 'bg-slate-900 border border-slate-800 rounded-lg px-4 py-3 flex justify-between items-center';
                row.innerHTML = `
                    <div class="text-sm font-medium text-white">${e.title}</div>
                    <div class="text-xs text-slate-400">${e.tickets_sold} sold · ${e.tickets_remaining} remaining · GHS ${Number(e.revenue).toFixed(2)}</div>
                `;
                list.appendChild(row);
            });
        })();
    </script>
</body>
</html>
