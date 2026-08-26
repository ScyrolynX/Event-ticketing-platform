<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - ScyrolynX Ticketing</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-950 min-h-screen text-slate-100">

    <header class="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-blue-500 rounded-md flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <span class="text-white font-bold text-sm">S</span>
                </div>
                <span class="text-lg font-bold text-white">ScyrolynX</span>
            </div>
            <nav class="flex gap-6 text-sm text-slate-400">
                <a href="/events" class="hover:text-violet-400">Events</a>
                <a href="/register" class="hover:text-violet-400">Register</a>
            </nav>
        </div>
    </header>

    <section class="max-w-md mx-auto px-6 py-20">
        <h1 class="text-2xl font-bold mb-6">Log in</h1>

        <p id="error" class="hidden text-red-400 text-sm mb-4"></p>

        <form id="login-form" class="space-y-4">
            <div>
                <label class="block text-sm text-slate-400 mb-1">Email</label>
                <input type="email" name="email" required
                    class="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-slate-100 focus:outline-none focus:border-violet-500">
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-slate-100 focus:outline-none focus:border-violet-500">
            </div>
            <button type="submit"
                class="w-full bg-violet-600 hover:bg-violet-500 text-white font-medium py-2 rounded-md transition">
                Log in
            </button>
        </form>

        <p class="text-sm text-slate-400 mt-4">
            No account yet? <a href="/register" class="text-violet-400">Register</a>
        </p>
    </section>

    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorEl = document.getElementById('error');
            errorEl.classList.add('hidden');

            const formData = new FormData(e.target);
            const payload = Object.fromEntries(formData.entries());

            const res = await fetch('/api/v1/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (!res.ok) {
                errorEl.textContent = data.message || 'Invalid credentials.';
                errorEl.classList.remove('hidden');
                return;
            }

            localStorage.setItem('token', data.token);
            window.location.href = '/events';
        });
    </script>
</body>
</html>