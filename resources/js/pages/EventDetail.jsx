import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Navbar from '../components/Navbar.jsx';

export default function EventDetail() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [event, setEvent] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [toast, setToast] = useState(null);
    const [discountCode, setDiscountCode] = useState('');

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
            navigate('/login');
            return;
        }

        const payload = { ticket_type_id: ticketTypeId, quantity: 1 };
        if (discountCode.trim()) payload.discount_code = discountCode.trim();

        const res = await fetch('/api/v1/orders', {
            method: 'POST',
            headers: {
                Authorization: 'Bearer ' + token,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify(payload),
        });

        if (res.status === 401) {
            localStorage.removeItem('token');
            navigate('/login');
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

    const eventDate = new Date(event.event_date);

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <Navbar />

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
                {event.image_url ? (
                    <div
                        className="h-48 rounded-2xl flex items-end p-8 mb-8 bg-cover bg-center relative"
                        style={{ backgroundImage: `url(${event.image_url})` }}
                    >
                        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent rounded-2xl" />
                        <h1 className="relative text-4xl font-bold text-white">{event.title}</h1>
                    </div>
                ) : (
                    <div className="h-48 bg-gradient-to-br from-violet-600 via-purple-600 to-blue-600 rounded-2xl flex items-end p-8 mb-8">
                        <h1 className="text-4xl font-bold text-white">{event.title}</h1>
                    </div>
                )}

                <div className="flex items-center gap-4 text-slate-400 mb-8 flex-wrap">
                    <span>📍 {event.venue}</span>
                    <span className="text-slate-700">·</span>
                    <span>📅 {eventDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</span>
                    <span className="text-slate-700">·</span>
                    <span>🕐 {eventDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })}</span>
                </div>

                {event.description && (
                    <div className="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-8">
                        <h2 className="text-sm font-semibold text-slate-400 uppercase tracking-wide mb-2">About This Event</h2>
                        <p className="text-slate-300 leading-relaxed">{event.description}</p>
                    </div>
                )}

                <div className="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-6">
                    <label className="block text-xs font-semibold text-slate-400 uppercase mb-2">Discount Code (optional)</label>
                    <input
                        type="text"
                        placeholder="e.g. SAVE10"
                        value={discountCode}
                        onChange={(e) => setDiscountCode(e.target.value)}
                        className="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm uppercase focus:outline-none focus:border-violet-500"
                    />
                </div>

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
