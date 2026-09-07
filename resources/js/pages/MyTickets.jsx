import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function MyTickets() {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }

        fetch('/api/v1/orders', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        })
            .then((res) => res.json())
            .then((data) => {
                setOrders(data.orders);
                setLoading(false);
            });
    }, []);

    async function requestRefund(orderId) {
        const res = await fetch(`/api/v1/orders/${orderId}/refund`, {
            method: 'POST',
            headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({}),
        });

        if (res.ok) {
            alert('Refund requested.');
        } else {
            const data = await res.json();
            alert(data.message || 'Refund request failed.');
        }
    }

    if (loading) return <div className="max-w-3xl mx-auto px-6 py-16 text-slate-400">Loading your orders...</div>;

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <header className="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
                <div className="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX</span>
                    <Link to="/" className="text-sm text-violet-400">← Events</Link>
                </div>
            </header>

            <main className="max-w-3xl mx-auto px-6 py-12">
                <h1 className="text-2xl font-bold mb-8">My Tickets</h1>

                {orders.length === 0 && <p className="text-slate-400">You haven't bought any tickets yet.</p>}

                <div className="space-y-4">
                    {orders.map((order) => {
                        const statusColor = {
                            paid: 'text-green-400 bg-green-500/10 border-green-500/30',
                            pending: 'text-amber-400 bg-amber-500/10 border-amber-500/30',
                            refunded: 'text-slate-400 bg-slate-500/10 border-slate-500/30',
                        }[order.status] || '';

                        return (
                            <div key={order.id} className="bg-slate-900 border border-slate-800 rounded-xl p-5">
                                <div className="flex justify-between items-start mb-3">
                                    <div>
                                        <div className="font-semibold text-white">Order #{order.id}</div>
                                        <div className="text-xs text-slate-500">
                                            {new Date(order.created_at).toLocaleString()}
                                        </div>
                                    </div>
                                    <span className={`text-xs px-2 py-1 rounded-full border ${statusColor}`}>
                                        {order.status}
                                    </span>
                                </div>

                                {order.order_items.map((item) => (
                                    <div key={item.id} className="flex justify-between text-sm text-slate-300 py-1">
                                        <span>{item.ticket_type.name} × {item.quantity}</span>
                                        <span>GHS {item.unit_price}</span>
                                    </div>
                                ))}

                                <div className="flex justify-between items-center mt-3">
                                    {order.status === 'paid' && (
                                        <button
                                            onClick={() => requestRefund(order.id)}
                                            className="text-xs px-3 py-1 rounded-full bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20"
                                        >
                                            Request Refund
                                        </button>
                                    )}
                                    <div className="font-bold text-violet-300 ml-auto">GHS {order.total_amount}</div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </main>
        </div>
    );
}
