import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

const BLANK_FORM = { name: '', contact_email: '', contact_phone: '', commission_rate: '' };

export default function ManagePromoters() {
    const [promoters, setPromoters] = useState([]);
    const [form, setForm] = useState(BLANK_FORM);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    // Editing an existing promoter: which one, and its form state.
    const [editingId, setEditingId] = useState(null);
    const [editForm, setEditForm] = useState(BLANK_FORM);

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
        setForm(BLANK_FORM);
        loadPromoters();
    }

    function startEdit(p) {
        setEditingId(p.id);
        setEditForm({
            name: p.name,
            contact_email: p.contact_email || '',
            contact_phone: p.contact_phone || '',
            commission_rate: p.commission_rate,
        });
        setError(null);
        setSuccess(null);
    }

    function cancelEdit() {
        setEditingId(null);
    }

    async function handleUpdate(e, id) {
        e.preventDefault();
        setError(null);
        setSuccess(null);

        const payload = { ...editForm };
        if (!payload.contact_email) delete payload.contact_email;
        if (!payload.contact_phone) delete payload.contact_phone;

        const res = await fetch(`/api/v1/manage/promoters/${id}`, {
            method: 'PATCH',
            headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            setError(data.message || 'Failed to update promoter.');
            return;
        }

        setSuccess('Promoter updated!');
        setEditingId(null);
        loadPromoters();
    }

    async function handleDelete(id, name) {
        if (!window.confirm(`Delete promoter "${name}"? Their events will become in-house events.`)) return;
        setError(null);
        setSuccess(null);

        const res = await fetch(`/api/v1/manage/promoters/${id}`, {
            method: 'DELETE',
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            setError(data.message || 'Failed to delete promoter.');
            return;
        }

        setSuccess('Promoter deleted.');
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
                            className="bg-gold-500 hover:bg-gold-400 text-stone-950 text-sm font-medium px-5 py-2 rounded-full transition-all duration-300 hover:scale-105 active:scale-95"
                        >
                            Add Promoter
                        </button>
                    </form>
                </div>

                <h2 className="font-semibold text-white mb-4">Existing Promoters</h2>
                <div className="space-y-3">
                    {promoters.map((p) => {
                        const isEditing = editingId === p.id;

                        if (isEditing) {
                            return (
                                <form
                                    key={p.id}
                                    onSubmit={(e) => handleUpdate(e, p.id)}
                                    className="bg-stone-900 border border-gold-500/40 rounded-lg px-4 py-3 space-y-2"
                                >
                                    <input
                                        required
                                        value={editForm.name}
                                        onChange={(e) => setEditForm({ ...editForm, name: e.target.value })}
                                        className="w-full bg-stone-800 border border-gold-500/40 rounded-md px-3 py-2 text-sm"
                                    />
                                    <input
                                        type="email"
                                        placeholder="Contact email (optional)"
                                        value={editForm.contact_email}
                                        onChange={(e) => setEditForm({ ...editForm, contact_email: e.target.value })}
                                        className="w-full bg-stone-800 border border-gold-500/40 rounded-md px-3 py-2 text-sm"
                                    />
                                    <input
                                        placeholder="Contact phone (optional)"
                                        value={editForm.contact_phone}
                                        onChange={(e) => setEditForm({ ...editForm, contact_phone: e.target.value })}
                                        className="w-full bg-stone-800 border border-gold-500/40 rounded-md px-3 py-2 text-sm"
                                    />
                                    <input
                                        type="number"
                                        step="0.01"
                                        required
                                        value={editForm.commission_rate}
                                        onChange={(e) => setEditForm({ ...editForm, commission_rate: e.target.value })}
                                        className="w-full bg-stone-800 border border-gold-500/40 rounded-md px-3 py-2 text-sm"
                                    />
                                    <div className="flex gap-2 pt-1">
                                        <button
                                            type="submit"
                                            className="bg-gold-500 hover:bg-gold-400 text-stone-950 text-xs font-medium px-4 py-1.5 rounded-full transition-all duration-200"
                                        >
                                            Save
                                        </button>
                                        <button
                                            type="button"
                                            onClick={cancelEdit}
                                            className="bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs font-medium px-4 py-1.5 rounded-full transition-all duration-200"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            );
                        }

                        return (
                            <div key={p.id} className="bg-stone-900 border border-stone-800 rounded-lg px-4 py-3 flex justify-between items-center">
                                <div>
                                    <div className="text-sm font-semibold text-white">{p.name}</div>
                                    <div className="text-xs text-stone-500">
                                        {p.events_count} event(s) · {p.commission_rate}% commission{p.contact_email ? ` · ${p.contact_email}` : ''}
                                    </div>
                                </div>
                                <div className="flex gap-3 shrink-0">
                                    <button
                                        onClick={() => startEdit(p)}
                                        className="text-xs text-gold-400 hover:text-gold-300 font-medium transition-colors duration-200"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onClick={() => handleDelete(p.id, p.name)}
                                        className="text-xs text-red-400 hover:text-red-300 font-medium transition-colors duration-200"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </main>
        </div>
    );
}
