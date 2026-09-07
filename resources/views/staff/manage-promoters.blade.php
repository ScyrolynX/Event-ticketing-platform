<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Promoters - ScyrolynX</title>
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
        <h1 class="text-2xl font-bold mb-8">Promoters</h1>

        <p id="error" class="hidden text-red-400 text-sm mb-4"></p>
        <p id="success" class="hidden text-green-400 text-sm mb-4"></p>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-10">
            <h2 class="font-semibold text-white mb-4">Add Promoter</h2>
            <form id="promoter-form" class="space-y-3">
                <input name="name" placeholder="Promoter / company name" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <input name="contact_email" type="email" placeholder="Contact email (optional)" class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <input name="contact_phone" placeholder="Contact phone (optional)" class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <input name="commission_rate" type="number" step="0.01" placeholder="Commission rate % (e.g. 10)" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <button type="submit" class="bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-5 py-2 rounded-full transition-all duration-300 active:scale-95">Add Promoter</button>
            </form>
        </div>

        <h2 class="font-semibold text-white mb-4">Existing Promoters</h2>
        <div id="promoters-list" class="space-y-3"></div>
    </main>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        const errorEl = document.getElementById('error');
        const successEl = document.getElementById('success');

        async function loadPromoters() {
            const res = await fetch('/api/v1/manage/promoters', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            });
            const data = await res.json();
            const list = document.getElementById('promoters-list');
            list.innerHTML = '';

            data.promoters.forEach(p => {
                const row = document.createElement('div');
                row.className = 'bg-slate-900 border border-slate-800 rounded-lg px-4 py-3';
                row.innerHTML = `
                    <div class="text-sm font-semibold text-white">${p.name}</div>
                    <div class="text-xs text-slate-500">${p.events_count} event(s) · ${p.commission_rate}% commission ${p.contact_email ? '· ' + p.contact_email : ''}</div>
                `;
                list.appendChild(row);
            });
        }

        document.getElementById('promoter-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');

            const payload = Object.fromEntries(new FormData(e.target).entries());

            const res = await fetch('/api/v1/manage/promoters', {
                method: 'POST',
                headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (!res.ok) {
                errorEl.textContent = data.message || 'Failed to add promoter.';
                errorEl.classList.remove('hidden');
                return;
            }

            successEl.textContent = 'Promoter added!';
            successEl.classList.remove('hidden');
            e.target.reset();
            loadPromoters();
        });

        loadPromoters();
    </script>
</body>
</html>
