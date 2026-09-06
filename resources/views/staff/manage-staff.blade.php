<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff - ScyrolynX</title>
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
        <h1 class="text-2xl font-bold mb-8">Manage Staff</h1>

        <p id="error" class="hidden text-red-400 text-sm mb-4"></p>
        <p id="success" class="hidden text-green-400 text-sm mb-4"></p>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-10">
            <h2 class="font-semibold text-white mb-4">Create Staff Account</h2>
            <form id="staff-form" class="space-y-3">
                <input name="name" placeholder="Full name" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <input name="email" type="email" placeholder="Email" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <input name="password" type="password" placeholder="Password (min 8 characters)" required minlength="8" class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                <select name="role" required class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm">
                    <option value="">Select a role...</option>
                    <option value="Box Office">Box Office</option>
                    <option value="Event Manager">Event Manager</option>
                    <option value="Admin">Admin</option>
                </select>
                <button type="submit" class="bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-5 py-2 rounded-full transition-all duration-300 active:scale-95">Create Staff Account</button>
            </form>
        </div>

        <h2 class="font-semibold text-white mb-4">Current Staff</h2>
        <div id="staff-list" class="space-y-3"></div>
    </main>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        const errorEl = document.getElementById('error');
        const successEl = document.getElementById('success');
        const roles = ['Box Office', 'Event Manager', 'Admin'];

        async function loadStaff() {
            const res = await fetch('/api/v1/manage/staff', {
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            });

            if (res.status === 403) {
                errorEl.textContent = 'Only Admins can manage staff.';
                errorEl.classList.remove('hidden');
                return;
            }

            const data = await res.json();
            const list = document.getElementById('staff-list');
            list.innerHTML = '';

            data.staff.forEach(user => {
                const currentRole = user.roles[0]?.name || '';
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between bg-slate-900 border border-slate-800 rounded-lg px-4 py-3';
                row.innerHTML = `
                    <div>
                        <div class="text-sm font-medium text-white">${user.name}</div>
                        <div class="text-xs text-slate-500">${user.email}</div>
                    </div>
                    <select class="role-select bg-slate-800 border border-slate-700 rounded-md px-2 py-1 text-xs" data-user-id="${user.id}">
                        ${roles.map(r => `<option value="${r}" ${r === currentRole ? 'selected' : ''}>${r}</option>`).join('')}
                        <option value="" ${!currentRole ? 'selected' : ''}>Remove staff access</option>
                    </select>
                `;
                list.appendChild(row);
            });

            document.querySelectorAll('.role-select').forEach(select => {
                select.addEventListener('change', async () => {
                    await fetch(`/api/v1/manage/staff/${select.dataset.userId}`, {
                        method: 'PATCH',
                        headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
                        body: JSON.stringify({ role: select.value || null }),
                    });
                    loadStaff();
                });
            });
        }

        document.getElementById('staff-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');

            const payload = Object.fromEntries(new FormData(e.target).entries());

            const res = await fetch('/api/v1/manage/staff', {
                method: 'POST',
                headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (!res.ok) {
                errorEl.textContent = data.message || 'Failed to create staff account.';
                errorEl.classList.remove('hidden');
                return;
            }

            successEl.textContent = 'Staff account created!';
            successEl.classList.remove('hidden');
            e.target.reset();
            loadStaff();
        });

        loadStaff();
    </script>
</body>
</html>
