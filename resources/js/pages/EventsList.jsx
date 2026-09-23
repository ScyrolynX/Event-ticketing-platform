import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Navbar from '../components/Navbar.jsx';

export default function EventsList() {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [filters, setFilters] = useState({ category: '', date_from: '', date_to: '', max_price: '' });

    useEffect(() => {
        loadEvents();
    }, [filters]);

    function loadEvents() {
        const params = new URLSearchParams();
        if (filters.category) params.set('category', filters.category);
        if (filters.date_from) params.set('date_from', filters.date_from);
        if (filters.date_to) params.set('date_to', filters.date_to);
        if (filters.max_price) params.set('max_price', filters.max_price);

        fetch('/api/v1/events?' + params.toString())
            .then((res) => {
                if (!res.ok) throw new Error('Failed to load events.');
                return res.json();
            })
            .then((data) => {
                setEvents(data.events);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    }

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <Navbar />

            <section className="relative overflow-hidden bg-gradient-to-br from-violet-900 via-slate-900 to-blue-900">
                <div className="absolute inset-0 opacity-30 bg-[radial-gradient(circle_at_20%_20%,_#8b5cf6_0%,_transparent_40%),radial-gradient(circle_at_80%_60%,_#3b82f6_0%,_transparent_40%)]" />
                <div className="relative max-w-6xl mx-auto px-6 py-20 text-center">
                    <span className="inline-block bg-violet-500/10 border border-violet-400/30 text-violet-300 text-xs font-semibold px-4 py-1.5 rounded-full mb-5 uppercase tracking-wide">
                        Powered by ScyrolynX
                    </span>
                    <h1 className="text-5xl font-bold mb-4 bg-gradient-to-r from-violet-300 via-blue-300 to-violet-300 bg-clip-text text-transparent">
                        Discover Upcoming Events
                    </h1>
                    <p className="text-slate-300 text-lg max-w-xl mx-auto">
                        Book tickets to the hottest concerts, meetups, and experiences — all in one place.
                    </p>
                </div>
            </section>

            <main className="max-w-6xl mx-auto px-6 py-16">
                <div className="flex items-center justify-between mb-6">
                    <h2 className="text-2xl font-bold text-white">Upcoming Events</h2>
                    <span className="text-sm text-slate-400">
                        {loading ? 'Loading...' : `${events.length} events found`}
                    </span>
                </div>

                <div className="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-8 grid grid-cols-2 md:grid-cols-4 gap-3">
                    <select
                        value={filters.category}
                        onChange={(e) => setFilters({ ...filters, category: e.target.value })}
                        className="bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm"
                    >
                        <option value="">All categories</option>
                        <option value="concert">Concert</option>
                        <option value="conference">Conference</option>
                        <option value="sports">Sports</option>
                        <option value="other">Other</option>
                    </select>
                    <input
                        type="date"
                        value={filters.date_from}
                        onChange={(e) => setFilters({ ...filters, date_from: e.target.value })}
                        className="bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm"
                    />
                    <input
                        type="date"
                        value={filters.date_to}
                        onChange={(e) => setFilters({ ...filters, date_to: e.target.value })}
                        className="bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm"
                    />
                    <input
                        type="number"
                        placeholder="Max price (GHS)"
                        value={filters.max_price}
                        onChange={(e) => setFilters({ ...filters, max_price: e.target.value })}
                        className="bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm"
                    />
                </div>

                {error && <p className="text-red-400 text-center py-12">{error}</p>}

                {!error && !loading && events.length === 0 && (
                    <p className="text-slate-400 text-center py-12">No events match these filters.</p>
                )}

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {events.map((event) => (
                        <div
                            key={event.id}
                            className="group bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden hover:border-violet-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-violet-500/10"
                        >
                            <div
                                className={`h-32 relative flex items-end p-4 ${event.image_url ? '' : 'bg-gradient-to-br from-violet-600 via-purple-600 to-blue-600'}`}
                                style={event.image_url ? { backgroundImage: `url(${event.image_url})`, backgroundSize: 'cover', backgroundPosition: 'center' } : undefined}
                            >
                                {event.image_url && (
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                                )}
                                <span className="relative bg-black/30 backdrop-blur text-white text-xs font-semibold px-3 py-1 rounded-full">
                                    {new Date(event.event_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                                </span>
                            </div>

                            <div className="p-6">
                                <div className="text-xs uppercase tracking-wide text-violet-400 mb-1">{event.category}</div>
                                <h3 className="text-xl font-bold text-white mb-1 group-hover:text-violet-300 transition-colors">
                                    {event.title}
                                </h3>
                                <div className="flex items-center gap-1.5 text-sm text-slate-400 mb-4">
                                    <span>📍 {event.venue}</span>
                                </div>

                                <div className="border-t border-slate-800 pt-4 mb-5 space-y-2">
                                    {event.ticket_types.map((tt) => (
                                        <div key={tt.id} className="flex justify-between items-center bg-slate-800/50 rounded-lg px-4 py-2.5">
                                            <span className="text-sm font-medium text-slate-300">{tt.name}</span>
                                            <span className="text-sm font-bold text-violet-300">GHS {Number(tt.price).toFixed(2)}</span>
                                        </div>
                                    ))}
                                </div>

                                <Link
                                    to={`/events/${event.id}`}
                                    className="block text-center bg-gradient-to-r from-violet-600 to-blue-600 hover:from-violet-500 hover:to-blue-500 text-white text-sm font-semibold py-2.5 rounded-lg transition-all duration-200 shadow-lg shadow-violet-900/50"
                                >
                                    View Details
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            </main>

            <footer className="border-t border-slate-800 mt-12">
                <div className="max-w-6xl mx-auto px-6 py-8 flex items-center justify-between text-sm text-slate-500">
                    <span>© {new Date().getFullYear()} ScyrolynX. All rights reserved.</span>
                    <span>Built with Laravel & React</span>
                </div>
            </footer>
        </div>
    );
}
