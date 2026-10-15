import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function ManagePromoters() {
    const [promoters, setPromoters] = useState([]);
    const [form, setForm] = useState({ name: '', contact_email: '', contact_phone: '', commission_rate: '' });
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }
        loadPromoters();
    }, []);

    async function loadPromoters() {
        const res = await fetch('/api/v1/manage/promoters', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        });
        const data = await res.json();
        setPromoters(data.promoters);
    }

    async function handleSubmit(e) {
        e.preventDefault();
        setError(null);
        setSuccess(null);

        const payload = { ...form };
        if (!payload.contact_email) delete payload.contact_email;
        if (!payload.contact_phone) delete payload.contact_phone;

        const res = await fetch('/api/v1/manage/promoters', {
            method: 'POST',
            headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (!res.ok) {
            setError(data.message || 'Failed to add promoter.');
            return;
        }

        setSuccess('Promoter added!');
        setForm({ name: '', contact_email: '', contact_phone: '', commission_rate: '' });
        loadPromoters();
    }

    return (
        <div className="min-h-screen bg-stone-950 text-stone-100">
            <header className="bg-stone-950/80 backdrop-blur border-b border-brand-600/25 sticky top-0 z-10">
                <div className="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX Staff</span>
                    <Link to="/staff" className="text-sm text-stone-400 hover:text-gold-400">← Dashboard</Link>
                </div>
            </header>

            <main className="max-w-3xl mx-auto px-6 py-12">
                <h1 className="text-2xl font-bold mb-8">Promoters</h1>

                {error && <p className="text-red-400 text-sm mb-4">{error}</p>}
                {success && <p className="text-green-400 text-sm mb-4">{success}</p>}

                <div className="bg-stone-900 border border-stone-800 rounded-xl p-6 mb-10">
                    <h2 className="font-semibold text-white mb-4">Add Promoter</h2>
                    <form onSubmit={handleSubmit} className="space-y-3">
                        <input
                            placeholder="Promoter / company name"
                            required
                            value={form.name}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <input
                            type="email"
                            placeholder="Contact email (optional)"
                            value={form.contact_email}
                            onChange={(e) => setForm({ ...form, contact_email: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <input
                            placeholder="Contact phone (optional)"
                            value={form.contact_phone}
                            onChange={(e) => setForm({ ...form, contact_phone: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <input
                            type="number"
                            step="0.01"
                            placeholder="Commission rate % (e.g. 10)"
                            required
                            value={form.commission_rate}
                            onChange={(e) => setForm({ ...form, commission_rate: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <button
                            type="submit"
                            className="bg-gold-500 hover:bg-gold-400 text-stone-950 text-sm font-medium px-5 py-2 rounded-full transition-all duration-300 active:scale-95"
                        >
                            Add Promoter
                        </button>
                    </form>
                </div>

                <h2 className="font-semibold text-white mb-4">Existing Promoters</h2>
                <div className="space-y-3">
                    {promoters.map((p) => (
                        <div key={p.id} className="bg-stone-900 border border-stone-800 rounded-lg px-4 py-3">
                            <div className="text-sm font-semibold text-white">{p.name}</div>
                            <div className="text-xs text-stone-500">
                                {p.events_count} event(s) · {p.commission_rate}% commission{p.contact_email ? ` · ${p.contact_email}` : ''}
                            </div>
                        </div>
                    ))}
                </div>
            </main>
        </div>
    );
}
