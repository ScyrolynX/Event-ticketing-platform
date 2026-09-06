<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Refunds - ScyrolynX</title>
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
        <h1 class="text-2xl font-bold mb-8">Refund Requests</h1>

        <p id="error" class="hidden text-red-400 text-sm mb-4"></p>
        <p id="empty" class="hidden text-slate-400">No refund requests.</p>
        <div id="refunds-list" class="space-y-4"></div>
    </main>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        async function loadRefunds() {
            const res = await fetch('/api/v1/manage/refunds', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            });

            if (res.status === 403) {
                document.getElementById('error').textContent = 'Only Admins can process refunds.';
                document.getElementById('error').classList.remove('hidden');
                return;
            }

            const data = await res.json();
            const list = document.getElementById('refunds-list');
            list.innerHTML = '';

            if (data.refunds.length === 0) {
                document.getElementById('empty').classList.remove('hidden');
                return;
            }

            data.refunds.forEach(refund => {
                const statusColor = {
                    pending: 'text-amber-400 bg-amber-500/10 border-amber-500/30',
                    approved: 'text-green-400 bg-green-500/10 border-green-500/30',
                    rejected: 'text-red-400 bg-red-500/10 border-red-500/30',
                }[refund.status];

                const actions = refund.status === 'pending'
                    ? `
                        <button class="approve-btn text-xs px-3 py-1 rounded-full bg-green-500/10 text-green-400 border border-green-500/30 hover:bg-green-500/20" data-id="${refund.id}">Approve</button>
                        <button class="reject-btn text-xs px-3 py-1 rounded-full bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20" data-id="${refund.id}">Reject</button>
                      `
                    : '';

                const card = document.createElement('div');
                card.className = 'bg-slate-900 border border-slate-800 rounded-xl p-5';
                card.innerHTML = `
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="font-semibold text-white">Order #${refund.order.id} — ${refund.order.user.name}</div>
                            <div class="text-xs text-slate-500">${refund.order.user.email}</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full border ${statusColor}">${refund.status}</span>
                    </div>
                    <div class="text-sm text-slate-300 mb-3">Total: GHS ${refund.order.total_amount}${refund.reason ? ' — Reason: ' + refund.reason : ''}</div>
                    <div class="flex gap-2">${actions}</div>
                `;
                list.appendChild(card);
            });

            document.querySelectorAll('.approve-btn').forEach(btn => {
                btn.addEventListener('click', () => processRefund(btn.dataset.id, 'approve'));
            });
            document.querySelectorAll('.reject-btn').forEach(btn => {
                btn.addEventListener('click', () => processRefund(btn.dataset.id, 'reject'));
            });
        }

        async function processRefund(id, action) {
            await fetch(`/api/v1/manage/refunds/${id}/${action}`, {
                method: 'POST',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            });
            loadRefunds();
        }

        loadRefunds();
    </script>
</body>
</html>
