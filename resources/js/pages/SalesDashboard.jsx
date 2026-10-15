import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function SalesDashboard() {
    const [data, setData] = useState(null);
    const [error, setError] = useState(null);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }

        fetch('/api/v1/manage/dashboard', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        }).then(async (res) => {
            if (res.status === 403) {
                setError('Only Admins can view the sales dashboard.');
                return;
            }
            setData(await res.json());
        });
    }, []);

    if (error) {
        return <div className="max-w-4xl mx-auto px-6 py-12 text-red-400">{error}</div>;
    }

    return (
        <div className="min-h-screen bg-stone-950 text-stone-100">
            <header className="bg-stone-950/80 backdrop-blur border-b border-brand-600/25 sticky top-0 z-10">
                <div className="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX Staff</span>
                    <Link to="/staff" className="text-sm text-stone-400 hover:text-gold-400">← Dashboard</Link>
                </div>
            </header>

            <main className="max-w-4xl mx-auto px-6 py-12">
                <h1 className="text-2xl font-bold mb-8">Sales Dashboard</h1>

                {!data ? (
                    <p className="text-stone-400">Loading...</p>
                ) : (
                    <>
                        <div className="grid grid-cols-2 gap-4 mb-10">
                            <div className="bg-stone-900 border border-stone-800 rounded-xl p-6">
                                <div className="text-3xl font-bold text-white">{Number(data.total_revenue).toFixed(2)}</div>
                                <div className="text-sm text-stone-400">Total Revenue (GHS)</div>
                            </div>
                            <div className="bg-stone-900 border border-stone-800 rounded-xl p-6">
                                <div className="text-3xl font-bold text-white">{data.total_tickets_sold}</div>
                                <div className="text-sm text-stone-400">Tickets Sold</div>
                            </div>
                        </div>

                        <h2 className="font-semibold text-white mb-4">By Event</h2>
                        <div className="space-y-3">
                            {data.events.map((e) => (
                                <div key={e.event_id} className="bg-stone-900 border border-stone-800 rounded-lg px-4 py-3 flex justify-between items-center">
                                    <div className="text-sm font-medium text-white">{e.title}</div>
                                    <div className="text-xs text-stone-400">
                                        {e.tickets_sold} sold · {e.tickets_remaining} remaining · GHS {Number(e.revenue).toFixed(2)}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </>
                )}
            </main>
        </div>
    );
}
