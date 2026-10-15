import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';

export default function Checkout() {
    const { id } = useParams();
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [paying, setPaying] = useState(false);
    const [done, setDone] = useState(false);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }

        fetch(`/api/v1/orders/${id}`, {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        })
            .then(async (res) => {
                if (!res.ok) throw new Error('Order not found.');
                return res.json();
            })
            .then((data) => {
                setOrder(data.order);
                setDone(data.order.status === 'paid');
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    }, [id]);

    async function completePayment() {
        setPaying(true);
        setError(null);

        try {
            const res = await fetch(`/api/v1/orders/${id}/complete-demo-payment`, {
                method: 'POST',
                headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            });
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message || 'Payment could not be completed.');
            }

            setOrder(data.order);
            setDone(true);
        } catch (err) {
            setError(err.message);
        } finally {
            setPaying(false);
        }
    }

    if (loading) {
        return <div className="max-w-lg mx-auto px-6 py-16 text-stone-400">Loading order...</div>;
    }

    if (error && !order) {
        return (
            <div className="max-w-lg mx-auto px-6 py-16 text-center">
                <p className="text-red-400 mb-4">{error}</p>
                <Link to="/my-tickets" className="text-gold-400 text-sm">← Back to My Tickets</Link>
            </div>
        );
    }

    const eventTitle = order.order_items?.[0]?.ticket_type?.event?.title;

    return (
        <div className="min-h-screen bg-stone-950 text-stone-100">
            <header className="bg-stone-950/80 backdrop-blur border-b border-brand-600/25 sticky top-0 z-10">
                <div className="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX</span>
                    <Link to="/my-tickets" className="text-sm text-gold-400">← My Tickets</Link>
                </div>
            </header>

            <main className="max-w-lg mx-auto px-6 py-12">
                <div className="bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs rounded-lg px-4 py-2 mb-6 text-center">
                    Sandbox mode — no live Paystack keys are configured, so this button stands in for the real payment redirect.
                </div>

                <div className="bg-stone-900 border border-stone-800 rounded-xl p-6 mb-6">
                    <div className="text-sm text-stone-400 mb-1">Order #{order.id}</div>
                    {eventTitle && <h1 className="text-xl font-bold text-white mb-4">{eventTitle}</h1>}

                    {order.order_items.map((item) => (
                        <div key={item.id} className="flex justify-between text-sm text-stone-300 py-1">
                            <span>{item.ticket_type.name} × {item.quantity}</span>
                            <span>GHS {item.unit_price}</span>
                        </div>
                    ))}

                    <div className="flex justify-between items-center mt-4 pt-4 border-t border-stone-800">
                        <span className="text-stone-400">Total</span>
                        <span className="text-xl font-bold text-gold-300">GHS {order.total_amount}</span>
                    </div>
                </div>

                {error && <p className="text-red-400 text-sm mb-4 text-center">{error}</p>}

                {done ? (
                    <div className="text-center">
                        <p className="text-green-400 mb-4">Payment complete — your tickets are ready.</p>
                        <Link
                            to="/my-tickets"
                            className="inline-block px-6 py-3 rounded-full bg-gold-500 hover:bg-gold-400 text-stone-950 font-medium"
                        >
                            View My Tickets
                        </Link>
                    </div>
                ) : (
                    <button
                        onClick={completePayment}
                        disabled={paying}
                        className="w-full py-3 rounded-full bg-gold-500 hover:bg-gold-400 disabled:opacity-50 text-stone-950 font-medium"
                    >
                        {paying ? 'Processing...' : 'Complete Payment'}
                    </button>
                )}
            </main>
        </div>
    );
}
