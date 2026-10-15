import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

const ROLES = ['Box Office', 'Event Manager', 'Admin'];

export default function ManageStaff() {
    const [staff, setStaff] = useState([]);
    const [form, setForm] = useState({ name: '', email: '', password: '', role: '' });
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }
        loadStaff();
    }, []);

    async function loadStaff() {
        const res = await fetch('/api/v1/manage/staff', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        });

        if (res.status === 403) {
            setError('Only Admins can manage staff.');
            return;
        }

        const data = await res.json();
        setStaff(data.staff);
    }

    async function handleCreate(e) {
        e.preventDefault();
        setError(null);
        setSuccess(null);

        const res = await fetch('/api/v1/manage/staff', {
            method: 'POST',
            headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(form),
        });

        const data = await res.json();

        if (!res.ok) {
            setError(data.message || 'Failed to create staff account.');
            return;
        }

        setSuccess('Staff account created!');
        setForm({ name: '', email: '', password: '', role: '' });
        loadStaff();
    }

    async function handleRoleChange(userId, role) {
        await fetch(`/api/v1/manage/staff/${userId}`, {
            method: 'PATCH',
            headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ role: role || null }),
        });
        loadStaff();
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
                <h1 className="text-2xl font-bold mb-8">Manage Staff</h1>

                {error && <p className="text-red-400 text-sm mb-4">{error}</p>}
                {success && <p className="text-green-400 text-sm mb-4">{success}</p>}

                <div className="bg-stone-900 border border-stone-800 rounded-xl p-6 mb-10">
                    <h2 className="font-semibold text-white mb-4">Create Staff Account</h2>
                    <form onSubmit={handleCreate} className="space-y-3">
                        <input
                            placeholder="Full name"
                            required
                            value={form.name}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <input
                            type="email"
                            placeholder="Email"
                            required
                            value={form.email}
                            onChange={(e) => setForm({ ...form, email: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <input
                            type="password"
                            placeholder="Password (min 8 characters)"
                            required
                            minLength={8}
                            value={form.password}
                            onChange={(e) => setForm({ ...form, password: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <select
                            required
                            value={form.role}
                            onChange={(e) => setForm({ ...form, role: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        >
                            <option value="">Select a role...</option>
                            {ROLES.map((r) => (
                                <option key={r} value={r}>{r}</option>
                            ))}
                        </select>
                        <button
                            type="submit"
                            className="bg-gold-500 hover:bg-gold-400 text-stone-950 text-sm font-medium px-5 py-2 rounded-full transition-all duration-300 active:scale-95"
                        >
                            Create Staff Account
                        </button>
                    </form>
                </div>

                <h2 className="font-semibold text-white mb-4">Current Staff</h2>
                <div className="space-y-3">
                    {staff.map((user) => {
                        const currentRole = user.roles[0]?.name || '';
                        return (
                            <div key={user.id} className="flex items-center justify-between bg-stone-900 border border-stone-800 rounded-lg px-4 py-3">
                                <div>
                                    <div className="text-sm font-medium text-white">{user.name}</div>
                                    <div className="text-xs text-stone-500">{user.email}</div>
                                </div>
                                <select
                                    value={currentRole}
                                    onChange={(e) => handleRoleChange(user.id, e.target.value)}
                                    className="bg-stone-800 border border-stone-700 rounded-md px-2 py-1 text-xs"
                                >
                                    {ROLES.map((r) => (
                                        <option key={r} value={r}>{r}</option>
                                    ))}
                                    <option value="">Remove staff access</option>
                                </select>
                            </div>
                        );
                    })}
                </div>
            </main>
        </div>
    );
}
