import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function ManageEvents() {
    const [events, setEvents] = useState([]);
    const [promoters, setPromoters] = useState([]);
    const [form, setForm] = useState({ title: '', category: '', promoter_id: '', description: '', venue: '', event_date: '' });
    const [imageFile, setImageFile] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const [ttForms, setTtForms] = useState({});
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }
        loadPromoters();
        loadEvents();
    }, []);

    async function loadPromoters() {
        const res = await fetch('/api/v1/manage/promoters', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        setPromoters(data.promoters);
    }

    async function loadEvents() {
        const res = await fetch('/api/v1/manage/events', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        });

        if (res.status === 403) {
            setError('You do not have permission to manage events.');
            return;
        }

        const data = await res.json();
        setEvents(data.events);
    }

    function handleImageChange(e) {
        const file = e.target.files[0];
        setImageFile(file || null);
        setImagePreview(file ? URL.createObjectURL(file) : null);
    }

    async function handleCreateEvent(e) {
        e.preventDefault();
        setError(null);
        setSuccess(null);

        const formData = new FormData();
        Object.entries(form).forEach(([key, value]) => {
            if (key === 'promoter_id' && !value) return;
            formData.append(key, value);
        });
        if (imageFile) formData.append('image', imageFile);

        const res = await fetch('/api/v1/manage/events', {
            method: 'POST',
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
            body: formData,
        });

        const data = await res.json();

        if (!res.ok) {
            setError(data.message || 'Failed to create event.');
            return;
        }

        setSuccess('Event created!');
        setForm({ title: '', category: '', promoter_id: '', description: '', venue: '', event_date: '' });
        setImageFile(null);
        setImagePreview(null);
        loadEvents();
    }

    function updateTtForm(eventId, field, value) {
        setTtForms((prev) => ({ ...prev, [eventId]: { ...prev[eventId], [field]: value } }));
    }

    async function handleAddTicketType(e, eventId) {
        e.preventDefault();
        const payload = ttForms[eventId] || {};

        const res = await fetch(`/api/v1/manage/events/${eventId}/ticket-types`, {
            method: 'POST',
            headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(payload),
        });

        if (res.ok) {
            setTtForms((prev) => ({ ...prev, [eventId]: {} }));
            loadEvents();
        }
    }

    return (
        <div className="min-h-screen bg-stone-950 text-stone-100">
            <header className="bg-stone-950/80 backdrop-blur border-b border-brand-600/25 sticky top-0 z-10">
                <div className="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX Staff</span>
                    <Link to="/staff" className="text-sm text-stone-400 hover:text-gold-400">← Dashboard</Link>
                </div>
            </header>

            <main className="max-w-3xl mx-auto px-6 py-12">
                <h1 className="text-2xl font-bold mb-8">Manage Events</h1>

                {error && <p className="text-red-400 text-sm mb-4">{error}</p>}
                {success && <p className="text-green-400 text-sm mb-4">{success}</p>}

                <div className="bg-stone-900 border border-stone-800 rounded-xl p-6 mb-10">
                    <h2 className="font-semibold text-white mb-4">Create Event</h2>
                    <form onSubmit={handleCreateEvent} className="space-y-3">
                        <input
                            placeholder="Event title"
                            required
                            value={form.title}
                            onChange={(e) => setForm({ ...form, title: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <select
                            required
                            value={form.category}
                            onChange={(e) => setForm({ ...form, category: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        >
                            <option value="">Select category...</option>
                            <option value="concert">Concert</option>
                            <option value="conference">Conference</option>
                            <option value="sports">Sports</option>
                            <option value="other">Other</option>
                        </select>
                        <select
                            value={form.promoter_id}
                            onChange={(e) => setForm({ ...form, promoter_id: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        >
                            <option value="">No promoter (in-house event)</option>
                            {promoters.map((p) => (
                                <option key={p.id} value={p.id}>{p.name} ({p.commission_rate}% commission)</option>
                            ))}
                        </select>
                        <textarea
                            placeholder="Description (optional)"
                            value={form.description}
                            onChange={(e) => setForm({ ...form, description: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <input
                            placeholder="Venue"
                            required
                            value={form.venue}
                            onChange={(e) => setForm({ ...form, venue: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <input
                            type="datetime-local"
                            required
                            value={form.event_date}
                            onChange={(e) => setForm({ ...form, event_date: e.target.value })}
                            className="w-full bg-stone-800 border border-stone-700 rounded-md px-3 py-2 text-sm"
                        />
                        <div>
                            <label className="block text-xs text-stone-400 mb-1">Event image (optional)</label>
                            <input
                                type="file"
                                accept="image/*"
                                onChange={handleImageChange}
                                className="w-full text-sm text-stone-300 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-stone-700 file:text-stone-200 file:text-xs"
                            />
                            {imagePreview && (
                                <img src={imagePreview} alt="" className="mt-3 h-32 rounded-lg object-cover border border-stone-700" />
                            )}
                        </div>
                        <button
                            type="submit"
                            className="bg-gold-500 hover:bg-gold-400 text-stone-950 text-sm font-medium px-5 py-2 rounded-full transition-all duration-300 active:scale-95"
                        >
                            Create Event
                        </button>
                    </form>
                </div>

                <div className="space-y-4">
                    {events.map((event) => (
                        <div key={event.id} className="bg-stone-900 border border-stone-800 rounded-xl p-5">
                            {event.image_url && (
                                <img src={event.image_url} alt="" className="w-full h-28 object-cover rounded-lg mb-3" />
                            )}
                            <div className="font-semibold text-white mb-1">{event.title}</div>
                            <div className="text-xs text-stone-500 mb-3">
                                {event.venue} — {new Date(event.event_date).toLocaleString()}
                            </div>

                            <div className="space-y-2 mb-3">
                                {event.ticket_types.map((tt) => (
                                    <div key={tt.id} className="flex justify-between text-sm bg-stone-800/50 rounded px-3 py-2">
                                        <span>{tt.name}</span>
                                        <span>GHS {tt.price} — {tt.quantity_available} available</span>
                                    </div>
                                ))}
                            </div>

                            <form onSubmit={(e) => handleAddTicketType(e, event.id)} className="flex gap-2">
                                <input
                                    placeholder="Ticket type name"
                                    required
                                    value={ttForms[event.id]?.name || ''}
                                    onChange={(e) => updateTtForm(event.id, 'name', e.target.value)}
                                    className="flex-1 bg-stone-800 border border-stone-700 rounded-md px-2 py-1 text-xs"
                                />
                                <input
                                    type="number"
                                    step="0.01"
                                    placeholder="Price"
                                    required
                                    value={ttForms[event.id]?.price || ''}
                                    onChange={(e) => updateTtForm(event.id, 'price', e.target.value)}
                                    className="w-20 bg-stone-800 border border-stone-700 rounded-md px-2 py-1 text-xs"
                                />
                                <input
                                    type="number"
                                    placeholder="Qty"
                                    required
                                    value={ttForms[event.id]?.quantity_available || ''}
                                    onChange={(e) => updateTtForm(event.id, 'quantity_available', e.target.value)}
                                    className="w-20 bg-stone-800 border border-stone-700 rounded-md px-2 py-1 text-xs"
                                />
                                <button type="submit" className="bg-stone-700 hover:bg-stone-600 text-xs px-3 py-1 rounded-md">
                                    Add
                                </button>
                            </form>
                        </div>
                    ))}
                </div>
            </main>
        </div>
    );
}
