<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard - ScyrolynX</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-950 min-h-screen text-slate-100">

    <header class="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
        <div class="max-w-3xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-blue-500 rounded-md flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <span class="text-white font-bold text-sm">S</span>
                </div>
                <span class="text-lg font-bold text-white">ScyrolynX Staff</span>
            </div>
            <a href="/events" class="text-sm text-slate-400 hover:text-violet-400">View public site</a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-16">
        <h1 class="text-2xl font-bold mb-2">Staff Dashboard</h1>
        <p id="role-label" class="text-slate-400 text-sm mb-10">Checking your access...</p>

        <div id="actions" class="grid gap-4 sm:grid-cols-2"></div>
    </main>

    <script>
        (async () => {
            const token = localStorage.getItem('token');
            if (!token) { window.location.href = '/login'; return; }

            const res = await fetch('/api/user', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
            });

            if (!res.ok) { window.location.href = '/login'; return; }

            const user = await res.json();
            const roles = user.roles ? user.roles.map(r => r.name) : [];

            if (roles.length === 0) {
                // No staff role at all, this account is a plain customer, send them home.
                window.location.href = '/events';
                return;
            }

            document.getElementById('role-label').textContent = 'Signed in as: ' + roles.join(', ');

            const actions = document.getElementById('actions');
            const cards = [];

            if (roles.includes('Box Office') || roles.includes('Admin') || roles.includes('Event Manager')) {
                cards.push({ href: '/check-in', title: 'Ticket Check-In', desc: 'Scan or enter a ticket code to admit a guest.' });
            }

            if (roles.includes('Admin') || roles.includes('Event Manager')) {
                cards.push({ href: '/manage/events', title: 'Manage Events', desc: 'Create events and ticket types.' });
            }

            if (roles.includes('Admin')) {
                cards.push({ href: '/manage/staff', title: 'Manage Staff', desc: 'Create staff accounts and assign roles.' });
            }

            if (roles.includes('Admin') || roles.includes('Event Manager')) {
                cards.push({ href: '/manage/discounts', title: 'Discount Codes', desc: 'Create sitewide or event-specific discount codes.' });
            }

            if (roles.includes('Admin')) {
                cards.push({ href: '/manage/dashboard', title: 'Sales Dashboard', desc: 'Revenue and tickets sold, per event.' });
            }

            if (roles.includes('Admin')) {
                cards.push({ href: '/manage/refunds', title: 'Refund Requests', desc: 'Approve or reject customer refund requests.' });
            }

            if (roles.includes('Admin')) {
                cards.push({ href: '/manage/promoters', title: 'Promoters', desc: 'Manage promoters and commission rates.' });
                cards.push({ href: '/manage/settlement-report', title: 'Settlement Report', desc: 'Amount owed per promoter after commission.' });
        }

            cards.forEach(c => {
                const el = document.createElement('a');
                el.href = c.href;
                el.className = 'block bg-slate-900 border border-slate-800 hover:border-violet-500/50 rounded-xl p-6 transition-all duration-300';
                el.innerHTML = `<div class="font-semibold text-white mb-1">${c.title}</div><div class="text-sm text-slate-400">${c.desc}</div>`;
                actions.appendChild(el);
            });
        })();
    </script>
</body>
</html>
