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
            <nav class="flex gap-2 text-sm">
                <a href="/events" class="nav-pill px-4 py-2 rounded-full bg-slate-800 text-slate-200 font-medium hover:bg-slate-700 transition-all duration-300">Events</a>
                <a href="/my-tickets" class="nav-pill px-4 py-2 rounded-full bg-violet-600 text-white font-medium transition-all duration-300">My Tickets</a>
            </nav>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-12">
        <h1 class="text-2xl font-bold mb-8">My Tickets</h1>

        <p id="loading" class="text-slate-400">Loading your orders...</p>
        <p id="empty" class="hidden text-slate-400">You haven't bought any tickets yet.</p>
        <div id="orders" class="space-y-4"></div>
    </main>

    <div id="qr-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-6">
        <div class="bg-slate-900 border border-violet-500/30 rounded-2xl p-8 max-w-sm w-full text-center">
            <img id="qr-modal-img" class="w-56 h-56 bg-white rounded-lg p-3 mx-auto mb-4" alt="Ticket QR code">
            <p id="qr-modal-label" class="text-sm text-slate-400 mb-4"></p>
            <button id="qr-modal-close" class="px-5 py-2 rounded-full bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium transition-all duration-300 active:scale-95">
                Close
            </button>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        const modal = document.getElementById('qr-modal');
        const modalImg = document.getElementById('qr-modal-img');
        const modalLabel = document.getElementById('qr-modal-label');

        document.getElementById('qr-modal-close').addEventListener('click', () => {
            modal.classList.add('hidden');
        });
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.add('hidden');
        });

        document.querySelectorAll('.nav-pill').forEach(pill => {
            pill.addEventListener('click', (e) => {
                e.preventDefault();
                pill.classList.add('shadow-lg', 'shadow-violet-500/60', 'ring-2', 'ring-violet-400');
                setTimeout(() => { window.location.href = pill.getAttribute('href'); }, 150);
            });
        });

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('copy-code-btn')) {
                navigator.clipboard.writeText(e.target.dataset.code);
                e.target.textContent = 'Copied!';
                setTimeout(() => { e.target.textContent = 'Copy code'; }, 1500);
            }
        });

        async function loadQrCode(ticketId, imgEl) {
            const res = await fetch(`/api/v1/tickets/${ticketId}/qr`, {
                headers: { 'Authorization': 'Bearer ' + token },
            });

            if (!res.ok) {
                imgEl.replaceWith(document.createTextNode('QR code unavailable'));
                return;
            }

            const svgText = await res.text();
            const blob = new Blob([svgText], { type: 'image/svg+xml' });
            const url = URL.createObjectURL(blob);
            imgEl.src = url;
            imgEl.dataset.fullSrc = url;
            imgEl.classList.add('cursor-pointer', 'hover:ring-2', 'hover:ring-violet-400', 'transition-all', 'duration-300');

            imgEl.addEventListener('click', () => {
                modalImg.src = imgEl.dataset.fullSrc;
                modalLabel.textContent = `Ticket #${ticketId}, present this at the door`;
                modal.classList.remove('hidden');
            });
        }

        (async () => {
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
            const qrTargets = [];

            data.orders.forEach(order => {
                const statusColor = order.status === 'paid'
                    ? 'text-green-400 bg-green-500/10 border-green-500/30'
                    : 'text-amber-400 bg-amber-500/10 border-amber-500/30';

                let itemsHtml = '';

                order.order_items.forEach(item => {
                    itemsHtml += `
                        <div class="flex justify-between text-sm text-slate-300 py-1">
                            <span>${item.ticket_type.name} × ${item.quantity}</span>
                            <span>GHS ${item.unit_price}</span>
                        </div>
                    `;

                    if (item.tickets.length > 0) {
                        item.tickets.forEach(t => {
                            const imgId = `qr-${t.id}`;
                            itemsHtml += `
                                <div class="flex items-center gap-3 pl-4 py-2">
                                    <img id="${imgId}" class="w-16 h-16 bg-white rounded-lg p-1" alt="Ticket QR code">
                                    <span class="text-xs text-slate-500">Ticket #${t.id}</span>
                                    <button class="copy-code-btn ml-auto text-xs px-3 py-1 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300" data-code="${t.unique_code}">Copy code</button>
                                </div>
                            `;
                            qrTargets.push(t.id);
                        });
                    } else {
                        itemsHtml += `<div class="text-xs text-slate-500 pl-4">Ticket not yet issued (awaiting payment confirmation)</div>`;
                    }
                });

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

            qrTargets.forEach(ticketId => {
                const imgEl = document.getElementById(`qr-${ticketId}`);
                loadQrCode(ticketId, imgEl);
            });
        })();
    </script>
</body>
</html>
