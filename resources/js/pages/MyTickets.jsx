import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Navbar from '../components/Navbar.jsx';

export default function MyTickets() {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [qrModal, setQrModal] = useState(null);
    const [copiedId, setCopiedId] = useState(null);
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

    async function openQr(ticketId) {
        const res = await fetch(`/api/v1/tickets/${ticketId}/qr`, {
            headers: { Authorization: 'Bearer ' + token },
        });
        if (!res.ok) return;
        const svgText = await res.text();
        const blob = new Blob([svgText], { type: 'image/svg+xml' });
        setQrModal({ url: URL.createObjectURL(blob), ticketId });
    }

    function copyCode(ticketId, code) {
        navigator.clipboard.writeText(code);
        setCopiedId(ticketId);
        setTimeout(() => setCopiedId(null), 1500);
    }

    if (loading) return <div className="max-w-3xl mx-auto px-6 py-16 text-stone-400">Loading your orders...</div>;

    return (
        <div className="min-h-screen bg-stone-950 text-stone-100">
            <Navbar />

            <main className="max-w-3xl mx-auto px-6 py-12">
                <h1 className="text-2xl font-bold mb-8">My Tickets</h1>

                {orders.length === 0 && <p className="text-stone-400">You haven't bought any tickets yet.</p>}

                <div className="space-y-4">
                    {orders.map((order) => {
                        const statusColor = {
                            paid: 'text-green-400 bg-green-500/10 border-green-500/30',
                            pending: 'text-amber-400 bg-amber-500/10 border-amber-500/30',
                            refunded: 'text-stone-400 bg-stone-500/10 border-stone-500/30',
                        }[order.status] || '';

                        return (
                            <div key={order.id} className="bg-stone-900 border border-stone-800 rounded-xl p-5">
                                <div className="flex justify-between items-start mb-3">
                                    <div>
                                        <div className="font-semibold text-white">Order #{order.id}</div>
                                        <div className="text-xs text-stone-500">
                                            {new Date(order.created_at).toLocaleString()}
                                        </div>
                                    </div>
                                    <span className={`text-xs px-2 py-1 rounded-full border ${statusColor}`}>
                                        {order.status}
                                    </span>
                                </div>

                                {order.order_items.map((item) => (
                                    <div key={item.id}>
                                        <div className="flex justify-between text-sm text-stone-300 py-1">
                                            <span>{item.ticket_type.name} × {item.quantity}</span>
                                            <span>GHS {item.unit_price}</span>
                                        </div>

                                        {item.tickets.length > 0 ? (
                                            item.tickets.map((t) => (
                                                <div key={t.id} className="flex items-center gap-3 pl-4 py-2">
                                                    <img
                                                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'/%3E"
                                                        onClick={() => openQr(t.id)}
                                                        className="w-10 h-10 bg-white rounded cursor-pointer border border-stone-700 hover:border-gold-400"
                                                        alt="View QR"
                                                    />
                                                    <span className="text-xs text-stone-500">Ticket #{t.id}</span>
                                                    <button
                                                        onClick={() => copyCode(t.id, t.unique_code)}
                                                        className="ml-auto text-xs px-3 py-1 rounded-full bg-stone-800 hover:bg-stone-700 text-stone-300"
                                                    >
                                                        {copiedId === t.id ? 'Copied!' : 'Copy code'}
                                                    </button>
                                                </div>
                                            ))
                                        ) : order.status === 'pending' ? (
                                            <div className="text-xs text-stone-500 pl-4">Awaiting payment confirmation</div>
                                        ) : null}
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
                                    {order.status === 'pending' && (
                                        <Link
                                            to={`/checkout/${order.id}`}
                                            className="text-xs px-3 py-1 rounded-full bg-gold-500 hover:bg-gold-400 text-stone-950"
                                        >
                                            Complete Payment
                                        </Link>
                                    )}
                                    <div className="font-bold text-gold-300 ml-auto">GHS {order.total_amount}</div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </main>

            {qrModal && (
                <div
                    className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-6"
                    onClick={() => setQrModal(null)}
                >
                    <div className="bg-stone-900 border border-gold-500/30 rounded-2xl p-8 max-w-sm w-full text-center" onClick={(e) => e.stopPropagation()}>
                        <img src={qrModal.url} className="w-56 h-56 bg-white rounded-lg p-3 mx-auto mb-4" alt="Ticket QR code" />
                        <p className="text-sm text-stone-400 mb-4">Ticket #{qrModal.ticketId}, present this at the door</p>
                        <button
                            onClick={() => setQrModal(null)}
                            className="px-5 py-2 rounded-full bg-gold-500 hover:bg-gold-400 text-stone-950 text-sm font-medium"
                        >
                            Close
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
