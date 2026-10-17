import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

const BLANK_FORM = { code: '', type: 'percentage', value: '', event_id: '' };

export default function ManageDiscounts() {
    const [codes, setCodes] = useState([]);
    const [form, setForm] = useState(BLANK_FORM);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    // Editing an existing code: which one, and its form state.
    const [editingId, setEditingId] = useState(null);
    const [editForm, setEditForm] = useState(BLANK_FORM);

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
        setForm(BLANK_FORM);
        loadCodes();
    }

    function startEdit(dc) {
        setEditingId(dc.id);
        setEditForm({
            code: dc.code,
            type: dc.type,
            value: dc.value,
            event_id: dc.event_id || '',
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
        if (!payload.event_id) delete payload.event_id;

        const res = await fetch(`/api/v1/manage/discount-codes/${id}`, {
            method: 'PATCH',
            headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            setError(data.message || 'Failed to update code.');
            return;
        }

        setSuccess('Discount code updated!');
        setEditingId(null);
        loadCodes();
    }

    async function handleDelete(id, code) {
        if (!window.confirm(`Delete code "${code}"?`)) return;
        setError(null);
        setSuccess(null);

        const res = await fetch(`/api/v1/manage/discount-codes/${id}`, {
            method: 'DELETE',
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            setError(data.message || 'Failed to delete code.');
            return;
        }

        setSuccess('Discount code deleted.');
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
                            className="bg-gold-500 hover:bg-gold-400 text-stone-950 text-sm font-medium px-5 py-2 rounded-full transition-all duration-300 hover:scale-105 active:scale-95"
                        >
                            Create Code
                        </button>
                    </form>
                </div>

                <h2 className="font-semibold text-white mb-4">Existing Codes</h2>
                <div className="space-y-3">
                    {codes.map((dc) => {
                        const isEditing = editingId === dc.id;

                        if (isEditing) {
                            return (
                                <form
                                    key={dc.id}
                                    onSubmit={(e) => handleUpdate(e, dc.id)}
                                    className="bg-stone-900 border border-gold-500/40 rounded-lg px-4 py-3 space-y-2"
                                >
                                    <input
                                        required
                                        value={editForm.code}
                                        onChange={(e) => setEditForm({ ...editForm, code: e.target.value })}
                                        className="w-full bg-stone-800 border border-gold-500/40 rounded-md px-3 py-2 text-sm uppercase"
                                    />
                                    <select
                                        value={editForm.type}
                                        onChange={(e) => setEditForm({ ...editForm, type: e.target.value })}
                                        className="w-full bg-stone-800 border border-gold-500/40 rounded-md px-3 py-2 text-sm"
                                    >
                                        <option value="percentage">Percentage off</option>
                                        <option value="fixed">Fixed amount off</option>
                                    </select>
                                    <input
                                        type="number"
                                        step="0.01"
                                        required
                                        value={editForm.value}
                                        onChange={(e) => setEditForm({ ...editForm, value: e.target.value })}
                                        className="w-full bg-stone-800 border border-gold-500/40 rounded-md px-3 py-2 text-sm"
                                    />
                                    <input
                                        type="number"
                                        placeholder="Event ID (leave blank for sitewide)"
                                        value={editForm.event_id}
                                        onChange={(e) => setEditForm({ ...editForm, event_id: e.target.value })}
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
                            <div key={dc.id} className="bg-stone-900 border border-stone-800 rounded-lg px-4 py-3 flex justify-between items-center">
                                <div>
                                    <div className="text-sm font-mono font-semibold text-gold-300">{dc.code}</div>
                                    <div className="text-xs text-stone-500">
                                        {dc.type === 'percentage' ? `${dc.value}% off` : `GHS ${dc.value} off`} — {dc.event_id ? `Event #${dc.event_id}` : 'Sitewide'}
                                    </div>
                                </div>
                                <div className="flex gap-3 shrink-0">
                                    <button
                                        onClick={() => startEdit(dc)}
                                        className="text-xs text-gold-400 hover:text-gold-300 font-medium transition-colors duration-200"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onClick={() => handleDelete(dc.id, dc.code)}
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
