<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Tickets - ScyrolynX</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-950 min-h-screen text-slate-100">

    <header class="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-blue-500 rounded-md flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <span class="text-white font-bold text-sm">S</span>
                </div>
                <span class="text-lg font-bold text-white">ScyrolynX</span>
            </div>
            <a href="/events" class="text-sm text-violet-400 hover:text-violet-300">← Back to Events</a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-12">
        <h1 class="text-2xl font-bold mb-8">My Tickets</h1>

        <p id="loading" class="text-slate-400">Loading your orders...</p>
        <p id="empty" class="hidden text-slate-400">You haven't bought any tickets yet.</p>
        <div id="orders" class="space-y-4"></div>
    </main>

    <script>
        (async () => {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            const res = await fetch('/api/v1/orders', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
            });

            document.getElementById('loading').classList.add('hidden');

            if (!res.ok) {
                document.getElementById('empty').textContent = 'Could not load orders.';
                document.getElementById('empty').classList.remove('hidden');
                return;
            }

            const data = await res.json();

            if (data.orders.length === 0) {
                document.getElementById('empty').classList.remove('hidden');
                return;
            }

            const container = document.getElementById('orders');

            data.orders.forEach(order => {
                const statusColor = order.status === 'paid'
                    ? 'text-green-400 bg-green-500/10 border-green-500/30'
                    : 'text-amber-400 bg-amber-500/10 border-amber-500/30';

                const itemsHtml = order.order_items.map(item => `
                    <div class="flex justify-between text-sm text-slate-300 py-1">
                        <span>${item.ticket_type.name} × ${item.quantity}</span>
                        <span>GHS ${item.unit_price}</span>
                    </div>
                    ${item.tickets.length > 0 ? item.tickets.map(t => `
                        <div class="text-xs text-slate-500 pl-4">Ticket code: ${t.unique_code.substring(0, 24)}...</div>
                    `).join('') : '<div class="text-xs text-slate-500 pl-4">Ticket not yet issued (awaiting payment confirmation)</div>'}
                `).join('');

                const card = document.createElement('div');
                card.className = 'bg-slate-900 border border-slate-800 rounded-xl p-5';
                card.innerHTML = `
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="font-semibold text-white">Order #${order.id}</div>
                            <div class="text-xs text-slate-500">${new Date(order.created_at).toLocaleString()}</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full border ${statusColor}">${order.status}</span>
                    </div>
                    ${itemsHtml}
                    <div class="text-right font-bold text-violet-300 mt-3">GHS ${order.total_amount}</div>
                `;
                container.appendChild(card);
            });
        })();
    </script>
</body>
</html>
