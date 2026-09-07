import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function StaffDashboard() {
    const [roles, setRoles] = useState([]);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }

        fetch('/api/user', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        })
            .then((res) => {
                if (!res.ok) {
                    throw new Error('Failed to fetch user');
                }
                return res.json();
            })
            .then((user) => {
                const userRoles = user.roles ? user.roles.map((r) => r.name) : [];

                if (userRoles.length === 0) {
                    navigate('/');
                    return;
                }

                setRoles(userRoles);
                setLoading(false);
            })
            .catch(() => {
                navigate('/login');
            });
    }, [token, navigate]);

    if (loading) {
        return <div className="max-w-3xl mx-auto px-6 py-16 text-slate-400">Checking your access...</div>;
    }

    const cards = [];

    if (roles.includes('Admin') || roles.includes('Event Manager') || roles.includes('Box Office')) {
        cards.push({ href: '/check-in', title: 'Ticket Check-In', desc: 'Scan or enter a ticket code to admit a guest.' });
    }

    if (roles.includes('Admin') || roles.includes('Event Manager')) {
        cards.push({ href: '/manage/events', title: 'Manage Events', desc: 'Create events and ticket types.' });
        cards.push({ href: '/manage/discounts', title: 'Discount Codes', desc: 'Create sitewide or event-specific codes.' });
        cards.push({ href: '/manage/promoters', title: 'Promoters', desc: 'Manage promoters and commission rates.' });
    }

    if (roles.includes('Admin')) {
        cards.push({ href: '/manage/staff', title: 'Manage Staff', desc: 'Create staff accounts and assign roles.' });
        cards.push({ href: '/manage/refunds', title: 'Refund Requests', desc: 'Approve or reject refund requests.' });
        cards.push({ href: '/manage/dashboard', title: 'Sales Dashboard', desc: 'Revenue and tickets sold, per event.' });
        cards.push({ href: '/manage/settlement-report', title: 'Settlement Report', desc: 'Amount owed per promoter.' });
    }

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <header className="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
                <div className="max-w-3xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX Staff</span>
                    <Link to="/" className="text-sm text-slate-400 hover:text-violet-400">View public site</Link>
                </div>
            </header>

            <main className="max-w-3xl mx-auto px-6 py-16">
                <h1 className="text-2xl font-bold mb-2">Staff Dashboard</h1>
                <p className="text-slate-400 text-sm mb-10">Signed in as: {roles.join(', ')}</p>

                <div className="grid gap-4 sm:grid-cols-2">
                    {cards.map((c) => (
                        <a

                            key={c.href}
                            href={c.href}
                            className="block bg-slate-900 border border-slate-800 hover:border-violet-500/50 rounded-xl p-6 transition-all duration-300"
                        >
                            <div className="font-semibold text-white mb-1">{c.title}</div>
                            <div className="text-sm text-slate-400">{c.desc}</div>
                        </a>
                    ))}
                </div>
            </main>
        </div>
    );
}
