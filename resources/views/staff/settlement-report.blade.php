<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settlement Report - ScyrolynX</title>
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
        <h1 class="text-2xl font-bold mb-8">Settlement Report</h1>
        <p class="text-slate-400 text-sm mb-8">Amount owed to each promoter after the company's commission.</p>

        <div id="report-list" class="space-y-3"></div>
    </main>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        (async () => {
            const res = await fetch('/api/v1/manage/settlement-report', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            });

            if (res.status === 403) {
                document.body.innerHTML = '<div class="max-w-4xl mx-auto px-6 py-12 text-red-400">Only Admins can view this report.</div>';
                return;
            }

            const data = await res.json();
            const list = document.getElementById('report-list');

            if (data.report.length === 0) {
                list.innerHTML = '<p class="text-slate-400">No promoters yet.</p>';
                return;
            }

            data.report.forEach(r => {
                const row = document.createElement('div');
                row.className = 'bg-slate-900 border border-slate-800 rounded-xl p-5';
                row.innerHTML = `
                    <div class="font-semibold text-white mb-2">${r.name}</div>
                    <div class="grid grid-cols-2 gap-2 text-sm text-slate-300">
                        <div>Tickets sold: <span class="text-white font-medium">${r.tickets_sold}</span></div>
                        <div>Commission rate: <span class="text-white font-medium">${r.commission_rate}%</span></div>
                        <div>Gross revenue: <span class="text-white font-medium">GHS ${Number(r.gross_revenue).toFixed(2)}</span></div>
                        <div>Commission kept: <span class="text-white font-medium">GHS ${Number(r.commission_kept).toFixed(2)}</span></div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-800 text-violet-300 font-bold">
                        Amount owed to promoter: GHS ${Number(r.amount_owed).toFixed(2)}
                    </div>
                `;
                list.appendChild(row);
            });
        })();
    </script>
</body>
</html>
