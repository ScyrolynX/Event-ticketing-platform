import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';

export default function EventDetail() {
    const { id } = useParams();
    const [event, setEvent] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [toast, setToast] = useState(null);

    useEffect(() => {
        fetch(`/api/v1/events/${id}`)
            .then((res) => {
                if (!res.ok) throw new Error('Failed to load event.');
                return res.json();
            })
            .then((data) => {
                setEvent(data.event);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    }, [id]);

    async function handleBuy(ticketTypeId) {
        const token = localStorage.getItem('token');

        if (!token) {
            window.location.href = '/login';
            return;
        }

        const res = await fetch('/api/v1/orders', {
            method: 'POST',
            headers: {
                Authorization: 'Bearer ' + token,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({ ticket_type_id: ticketTypeId, quantity: 1 }),
        });

        if (res.status === 401) {
            localStorage.removeItem('token');
            window.location.href = '/login';
            return;
        }

        const data = await res.json();

        if (!res.ok) {
            setToast({ text: data.message || 'Purchase failed.', error: true });
            return;
        }

        setToast({ text: 'Order placed! Total: GHS ' + data.order.total_amount, error: false });
    }

    if (loading) return <div className="max-w-4xl mx-auto px-6 py-16 text-slate-400">Loading...</div>;
    if (error) return <div className="max-w-4xl mx-auto px-6 py-16 text-red-400">{error}</div>;

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <header className="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
                <div className="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX</span>
                    <Link to="/" className="text-sm text-violet-400">← Back to Events</Link>
                </div>
            </header>

            {toast && (
                <div
                    className={
                        'fixed top-20 right-6 z-50 px-4 py-3 rounded-lg text-sm border ' +
                        (toast.error
                            ? 'bg-red-500/10 text-red-400 border-red-500/30'
                            : 'bg-green-500/10 text-green-400 border-green-500/30')
                    }
                >
                    {toast.text}
                </div>
            )}

            <main className="max-w-4xl mx-auto px-6 py-12">
                <div className="h-48 bg-gradient-to-br from-violet-600 via-purple-600 to-blue-600 rounded-2xl flex items-end p-8 mb-8">
                    <h1 className="text-4xl font-bold text-white">{event.title}</h1>
                </div>

                <div className="text-slate-400 mb-8">📍 {event.venue}</div>

                <div className="bg-slate-900 border border-slate-800 rounded-xl p-6">
                    <h2 className="text-sm font-semibold text-slate-400 uppercase mb-4">Available Tickets</h2>
                    <div className="space-y-3">
                        {event.ticket_types.map((tt) => (
                            <div key={tt.id} className="flex justify-between items-center bg-slate-800/50 rounded-lg px-5 py-4">
                                <div>
                                    <div className="font-semibold text-white">{tt.name}</div>
                                    <div className="text-xs text-slate-400">
                                        {tt.quantity_available - tt.quantity_sold} remaining
                                    </div>
                                </div>
                                <div className="flex items-center gap-4">
                                    <span className="text-lg font-bold text-violet-300">
                                        GHS {Number(tt.price).toFixed(2)}
                                    </span>
                                    <button
                                        onClick={() => handleBuy(tt.id)}
                                        className="bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-4 py-2 rounded-md transition-all duration-300 active:scale-95"
                                    >
                                        Buy
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </main>
        </div>
    );
}
