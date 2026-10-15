import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function Login() {
    const [form, setForm] = useState({ email: '', password: '' });
    const [error, setError] = useState(null);
    const navigate = useNavigate();

    async function handleSubmit(e) {
        e.preventDefault();
        setError(null);

        const res = await fetch('/api/v1/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(form),
        });

        const data = await res.json();

        if (!res.ok) {
            setError(data.message || 'Invalid credentials.');
            return;
        }

        localStorage.setItem('token', data.token);
        const roles = data.user.roles ? data.user.roles.map((r) => r.name) : [];
        navigate(roles.length > 0 ? '/staff' : '/');
    }

    return (
        <div className="min-h-screen bg-stone-950 text-stone-100">
            <header className="bg-stone-950/80 backdrop-blur border-b border-brand-600/25 sticky top-0 z-10">
                <div className="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX</span>
                    <nav className="flex gap-6 text-sm text-stone-400">
                        <Link to="/" className="hover:text-gold-400">Events</Link>
                        <Link to="/register" className="hover:text-gold-400">Register</Link>
                    </nav>
                </div>
            </header>

            <section className="max-w-md mx-auto px-6 py-20">
                <h1 className="text-2xl font-bold mb-6">Log in</h1>

                {error && <p className="text-red-400 text-sm mb-4">{error}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm text-stone-400 mb-1">Email</label>
                        <input
                            type="email"
                            required
                            value={form.email}
                            onChange={(e) => setForm({ ...form, email: e.target.value })}
                            className="w-full bg-stone-900 border border-stone-700 rounded-md px-3 py-2 focus:outline-none focus:border-gold-500"
                        />
                    </div>
                    <div>
                        <label className="block text-sm text-stone-400 mb-1">Password</label>
                        <input
                            type="password"
                            required
                            value={form.password}
                            onChange={(e) => setForm({ ...form, password: e.target.value })}
                            className="w-full bg-stone-900 border border-stone-700 rounded-md px-3 py-2 focus:outline-none focus:border-gold-500"
                        />
                    </div>
                    <button
                        type="submit"
                        className="w-full bg-gold-500 hover:bg-gold-400 text-stone-950 font-medium py-2 rounded-md transition-all duration-300 active:scale-95"
                    >
                        Log in
                    </button>
                </form>

                <p className="text-sm text-stone-400 mt-4">
                    No account yet? <Link to="/register" className="text-gold-400">Register</Link>
                </p>
            </section>
        </div>
    );
}

