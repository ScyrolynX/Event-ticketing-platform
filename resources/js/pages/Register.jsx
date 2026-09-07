import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function Register() {
    const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' });
    const [error, setError] = useState(null);
    const navigate = useNavigate();

    function update(field, value) {
        setForm({ ...form, [field]: value });
    }

    async function handleSubmit(e) {
        e.preventDefault();
        setError(null);

        const res = await fetch('/api/v1/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(form),
        });

        const data = await res.json();

        if (!res.ok) {
            setError(data.message || 'Something went wrong.');
            return;
        }

        localStorage.setItem('token', data.token);
        navigate('/');
    }

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <header className="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
                <div className="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX</span>
                    <nav className="flex gap-6 text-sm text-slate-400">
                        <Link to="/" className="hover:text-violet-400">Events</Link>
                        <Link to="/login" className="hover:text-violet-400">Login</Link>
                    </nav>
                </div>
            </header>

            <section className="max-w-md mx-auto px-6 py-20">
                <h1 className="text-2xl font-bold mb-6">Create an account</h1>

                {error && <p className="text-red-400 text-sm mb-4">{error}</p>}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm text-slate-400 mb-1">Name</label>
                        <input required value={form.name} onChange={(e) => update('name', e.target.value)} className="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2" />
                    </div>
                    <div>
                        <label className="block text-sm text-slate-400 mb-1">Email</label>
                        <input type="email" required value={form.email} onChange={(e) => update('email', e.target.value)} className="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2" />
                    </div>
                    <div>
                        <label className="block text-sm text-slate-400 mb-1">Password</label>
                        <input type="password" required minLength={8} value={form.password} onChange={(e) => update('password', e.target.value)} className="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2" />
                    </div>
                    <div>
                        <label className="block text-sm text-slate-400 mb-1">Confirm password</label>
                        <input type="password" required minLength={8} value={form.password_confirmation} onChange={(e) => update('password_confirmation', e.target.value)} className="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2" />
                    </div>
                    <button type="submit" className="w-full bg-violet-600 hover:bg-violet-500 text-white font-medium py-2 rounded-md transition-all duration-300 active:scale-95">
                        Register
                    </button>
                </form>

                <p className="text-sm text-slate-400 mt-4">
                    Already have an account? <Link to="/login" className="text-violet-400">Log in</Link>
                </p>
            </section>
        </div>
    );
}
