import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';

export default function EventsList() {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetch('/api/v1/events')
            .then((res) => {
                if (!res.ok) throw new Error('Failed to load events.');
                return res.json();
            })
            .then((data) => {
                setEvents(data.events || data);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    }, []);

    if (loading) {
        return <div className="max-w-6xl mx-auto px-6 py-16 text-slate-400">Loading events...</div>;
    }

    if (error) {
        return <div className="max-w-6xl mx-auto px-6 py-16 text-red-400">{error}</div>;
    }

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <header className="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
                <div className="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-8 h-8 bg-gradient-to-br from-violet-500 to-blue-500 rounded-md flex items-center justify-center shadow-lg shadow-violet-500/30">
                            <span className="text-white font-bold text-sm">S</span>
                        </div>
                        <span className="text-lg font-bold text-white">ScyrolynX</span>
                    </div>
                </div>
            </header>

            <main className="max-w-6xl mx-auto px-6 py-16">
                <h2 className="text-2xl font-bold text-white mb-8">Upcoming Events</h2>

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {events.map((event) => (
                        <div
                            key={event.id}
                            className="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden hover:border-violet-500/50 transition-all duration-300"
                        >
                            <div className="h-32 bg-gradient-to-br from-violet-600 via-purple-600 to-blue-600" />

                            <div className="p-6">
                                <h3 className="text-xl font-bold text-white mb-1">{event.title}</h3>
                                <div className="text-sm text-slate-400 mb-4">📍 {event.venue}</div>

                                <Link
                                    to={`/react/events/${event.id}`}
                                    className="block text-center bg-gradient-to-r from-violet-600 to-blue-600 text-white text-sm font-semibold py-2.5 rounded-lg"
                                >
                                    View Details
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            </main>
        </div>
    );
}
