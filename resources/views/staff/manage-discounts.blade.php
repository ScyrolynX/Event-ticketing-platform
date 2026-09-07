<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discount Codes - ScyrolynX</title>
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
        <h1 class="text-2xl font-bold mb-8">Discount Codes</h1>

        <p id="error" class="hidden text-red-400 text-sm mb-4"></p>
        <p id="success" class="hidden text-green-400 text-sm mb-4"></p>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-10">
            <h2 class="font-semibold text-white mb-4">Create Discount Code</h2>
            <form id="discount-form" class="space-y-3">
                <input name="code" placeholder="Code (e.g. SAVE10)" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm uppercase">
                <select name="type" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                    <option value="percentage">Percentage off</option>
                    <option value="fixed">Fixed amount off</option>
                </select>
                <input name="value" type="number" step="0.01" placeholder="Value (e.g. 10)" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <input name="event_id" type="number" placeholder="Event ID (leave blank for sitewide)" class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <button type="submit" class="bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-5 py-2 rounded-full transition-all duration-300 active:scale-95">Create Code</button>
            </form>
        </div>

        <h2 class="font-semibold text-white mb-4">Existing Codes</h2>
        <div id="codes-list" class="space-y-3"></div>
    </main>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        const errorEl = document.getElementById('error');
        const successEl = document.getElementById('success');

        async function loadCodes() {
            const res = await fetch('/api/v1/manage/discount-codes', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            });
            const data = await res.json();
            const list = document.getElementById('codes-list');
            list.innerHTML = '';

            data.discount_codes.forEach(dc => {
                const row = document.createElement('div');
                row.className = 'bg-slate-900 border border-slate-800 rounded-lg px-4 py-3 flex justify-between items-center';
                row.innerHTML = `
                    <div>
                        <div class="text-sm font-mono font-semibold text-violet-300">${dc.code}</div>
                        <div class="text-xs text-slate-500">${dc.type === 'percentage' ? dc.value + '% off' : 'GHS ' + dc.value + ' off'} — ${dc.event_id ? 'Event #' + dc.event_id : 'Sitewide'}</div>
                    </div>
                `;
                list.appendChild(row);
            });
        }

        document.getElementById('discount-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');

            const payload = Object.fromEntries(new FormData(e.target).entries());
            if (!payload.event_id) delete payload.event_id;

            const res = await fetch('/api/v1/manage/discount-codes', {
                method: 'POST',
                headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (!res.ok) {
                errorEl.textContent = data.message || 'Failed to create code.';
                errorEl.classList.remove('hidden');
                return;
            }

            successEl.textContent = 'Discount code created!';
            successEl.classList.remove('hidden');
            e.target.reset();
            loadCodes();
        });

        loadCodes();
    </script>
</body>
</html>
