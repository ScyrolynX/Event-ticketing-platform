import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function CheckIn() {
    const [code, setCode] = useState('');
    const [result, setResult] = useState(null);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    if (!token) {
        navigate('/login');
        return null;
    }

    async function handleSubmit(e) {
        e.preventDefault();

        const res = await fetch('/api/v1/check-in', {
            method: 'POST',
            headers: {
                Authorization: 'Bearer ' + token,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({ unique_code: code.trim() }),
        });

        const data = await res.json();

        if (res.status === 403) {
            setResult({ type: 'error', icon: '🚫', title: 'Not authorized', detail: 'Your account does not have check-in access.' });
        } else if (res.ok) {
            setResult({ type: 'success', icon: '✅', title: 'Ticket accepted', detail: `Ticket #${data.ticket_id}, admit guest.` });
        } else {
            setResult({ type: 'warn', icon: '⚠️', title: data.message || 'Rejected', detail: null });
        }

        setCode('');
    }

    const resultClasses = {
        success: 'bg-green-500/10 border-green-500/40 text-green-300',
        error: 'bg-red-500/10 border-red-500/40 text-red-300',
        warn: 'bg-amber-500/10 border-amber-500/40 text-amber-300',
    };

    return (
        <div className="min-h-screen bg-stone-950 text-stone-100">
            <header className="bg-stone-950/80 backdrop-blur border-b border-brand-600/25 sticky top-0 z-10">
                <div className="max-w-2xl mx-auto px-6 py-5 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-8 h-8 bg-gradient-to-br from-brand-600 to-gold-500 rounded-md flex items-center justify-center shadow-lg shadow-brand-600/40">
                            <span className="text-white font-bold text-sm">S</span>
                        </div>
                        <span className="text-lg font-bold text-white">ScyrolynX Staff</span>
                    </div>
                    <Link to="/" className="text-sm text-stone-400 hover:text-gold-400">← Exit</Link>
                </div>
            </header>

            <main className="max-w-md mx-auto px-6 py-16 text-center">
                <h1 className="text-2xl font-bold mb-2">Ticket Check-In</h1>
                <p className="text-stone-400 text-sm mb-10">Paste or scan a ticket's code below to admit it.</p>

                {result && (
                    <div className={`rounded-2xl p-8 mb-6 border ${resultClasses[result.type]}`}>
                        <div className="text-4xl mb-2">{result.icon}</div>
                        <div className="font-semibold">{result.title}</div>
                        {result.detail && <div className="text-sm mt-1">{result.detail}</div>}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <textarea
                        value={code}
                        onChange={(e) => setCode(e.target.value)}
                        rows={3}
                        placeholder="Ticket code..."
                        required
                        className="w-full bg-stone-900 border border-stone-700 rounded-md px-3 py-2 text-stone-100 text-xs focus:outline-none focus:border-gold-500"
                    />
                    <button
                        type="submit"
                        className="w-full bg-gold-500 hover:bg-gold-400 text-stone-950 font-medium py-3 rounded-full transition-all duration-300 active:scale-95"
                    >
                        Check In
                    </button>
                </form>
            </main>
        </div>
    );
}
