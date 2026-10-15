import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function ManageDiscounts() {
    const [codes, setCodes] = useState([]);
    const [form, setForm] = useState({ code: '', type: 'percentage', value: '', event_id: '' });
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }
        loadCodes();
    }, []);

    async function loadCodes() {
        const res = await fetch('/api/v1/manage/discount-codes', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        });
        const data = await res.json();
        setCodes(data.discount_codes);
    }

    async function handleSubmit(e) {
        e.preventDefault();
        setError(null);
        setSuccess(null);

        const payload = { ...form };
        if (!payload.event_id) delete payload.event_id;

        const res = await fetch('/api/v1/manage/discount-codes', {
            method: 'POST',
            headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (!res.ok) {
            setError(data.message || 'Failed to create code.');
            return;
        }

        setSuccess('Discount code created!');
        setForm({ code: '', type: 'percentage', value: '', event_id: '' });
        loadCodes();
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
                <h1 className="text-2xl font-bold mb-8">Discount Codes</h1>

                {error && <p className="text-red-400 text-sm mb-4">{error}</p>}
                {success && <p className="text-green-400 text-sm mb-4">{success}</p>}

                <div className="bg-stone-900 border border-stone-800 rounded-xl p-6 mb-10">
                    <h2 className="font-semibold text-white mb-4">Create Discount Code</h2>
                    <form onSubmit={handleSubmit} className="space-y-3">
                        <input
                            placeholder="Code (e.g. SAVE10)"
                            required
                            value={form.code}
                            onChange={(e) => setForm({ ...form, code: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm uppercase"
                        />
                        <select
                            value={form.type}
                            onChange={(e) => setForm({ ...form, type: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        >
                            <option value="percentage">Percentage off</option>
                            <option value="fixed">Fixed amount off</option>
                        </select>
                        <input
                            type="number"
                            step="0.01"
                            placeholder="Value (e.g. 10)"
                            required
                            value={form.value}
                            onChange={(e) => setForm({ ...form, value: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <input
                            type="number"
                            placeholder="Event ID (leave blank for sitewide)"
                            value={form.event_id}
                            onChange={(e) => setForm({ ...form, event_id: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <button
                            type="submit"
                            className="bg-gold-500 hover:bg-gold-400 text-stone-950 text-sm font-medium px-5 py-2 rounded-full transition-all duration-300 active:scale-95"
                        >
                            Create Code
                        </button>
                    </form>
                </div>

                <h2 className="font-semibold text-white mb-4">Existing Codes</h2>
                <div className="space-y-3">
                    {codes.map((dc) => (
                        <div key={dc.id} className="bg-stone-900 border border-stone-800 rounded-lg px-4 py-3 flex justify-between items-center">
                            <div>
                                <div className="text-sm font-mono font-semibold text-gold-300">{dc.code}</div>
                                <div className="text-xs text-stone-500">
                                    {dc.type === 'percentage' ? `${dc.value}% off` : `GHS ${dc.value} off`} — {dc.event_id ? `Event #${dc.event_id}` : 'Sitewide'}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </main>
        </div>
    );
}
