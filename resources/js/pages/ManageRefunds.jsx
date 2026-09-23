import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function ManageRefunds() {
    const [refunds, setRefunds] = useState([]);
    const [error, setError] = useState(null);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }
        loadRefunds();
    }, []);

    async function loadRefunds() {
        const res = await fetch('/api/v1/manage/refunds', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        });

        if (res.status === 403) {
            setError('Only Admins can process refunds.');
            return;
        }

        const data = await res.json();
        setRefunds(data.refunds);
    }

    async function processRefund(id, action) {
        await fetch(`/api/v1/manage/refunds/${id}/${action}`, {
            method: 'POST',
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        });
        loadRefunds();
    }

    const statusColor = {
        pending: 'text-amber-400 bg-amber-500/10 border-amber-500/30',
        approved: 'text-green-400 bg-green-500/10 border-green-500/30',
        rejected: 'text-red-400 bg-red-500/10 border-red-500/30',
    };

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <header className="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
                <div className="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX Staff</span>
                    <Link to="/staff" className="text-sm text-slate-400 hover:text-violet-400">← Dashboard</Link>
                </div>
            </header>

            <main className="max-w-3xl mx-auto px-6 py-12">
                <h1 className="text-2xl font-bold mb-8">Refund Requests</h1>

                {error && <p className="text-red-400 text-sm mb-4">{error}</p>}
                {!error && refunds.length === 0 && <p className="text-slate-400">No refund requests.</p>}

                <div className="space-y-4">
                    {refunds.map((refund) => (
                        <div key={refund.id} className="bg-slate-900 border border-slate-800 rounded-xl p-5">
                            <div className="flex justify-between items-start mb-2">
                                <div>
                                    <div className="font-semibold text-white">Order #{refund.order.id} — {refund.order.user.name}</div>
                                    <div className="text-xs text-slate-500">{refund.order.user.email}</div>
                                </div>
                                <span className={`text-xs px-2 py-1 rounded-full border ${statusColor[refund.status]}`}>
                                    {refund.status}
                                </span>
                            </div>
                            <div className="text-sm text-slate-300 mb-3">
                                Total: GHS {refund.order.total_amount}{refund.reason ? ` — Reason: ${refund.reason}` : ''}
                            </div>
                            {refund.status === 'pending' && (
                                <div className="flex gap-2">
                                    <button
                                        onClick={() => processRefund(refund.id, 'approve')}
                                        className="text-xs px-3 py-1 rounded-full bg-green-500/10 text-green-400 border border-green-500/30 hover:bg-green-500/20"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        onClick={() => processRefund(refund.id, 'reject')}
                                        className="text-xs px-3 py-1 rounded-full bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20"
                                    >
                                        Reject
                                    </button>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </main>
        </div>
    );
}
