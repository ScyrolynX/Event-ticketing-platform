import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function Navbar() {
    const [roles, setRoles] = useState([]);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) return;

        fetch('/api/user', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        })
            .then((res) => (res.ok ? res.json() : null))
            .then((user) => {
                if (user?.roles) setRoles(user.roles.map((r) => r.name));
            })
            .catch(() => {});
    }, [token]);

    function handleLogout() {
        localStorage.removeItem('token');
        navigate('/login');
    }

    return (
        <header className="bg-stone-950/80 backdrop-blur border-b border-brand-600/25 sticky top-0 z-10">
            <div className="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
                <Link to="/" className="flex items-center gap-2 group">
                    <div className="sheen-surface w-8 h-8 bg-gradient-to-br from-brand-600 to-gold-500 rounded-md flex items-center justify-center shadow-lg shadow-brand-600/40 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                        <span className="text-stone-950 font-bold text-sm">S</span>
                    </div>
                    <span className="text-lg font-bold text-white">ScyrolynX</span>
                </Link>

                <nav className="flex items-center gap-5 text-sm">
                    <Link to="/" className="text-stone-400 hover:text-gold-400 transition-colors duration-300">Events</Link>

                    {token ? (
                        <>
                            <Link to="/my-tickets" className="text-stone-400 hover:text-gold-400 transition-colors duration-300">My Tickets</Link>
                            {roles.length > 0 && (
                                <Link to="/staff" className="text-stone-400 hover:text-gold-400 transition-colors duration-300">Staff</Link>
                            )}
                            <button onClick={handleLogout} className="text-stone-400 hover:text-red-400 transition-colors duration-300">Logout</button>
                        </>
                    ) : (
                        <>
                            <Link to="/login" className="text-stone-400 hover:text-gold-400 transition-colors duration-300">Login</Link>
                            <Link
                                to="/register"
                                className="bg-gold-500 hover:bg-gold-400 text-stone-950 px-4 py-1.5 rounded-full font-medium shadow-md shadow-gold-500/30 hover:shadow-gold-400/50 hover:scale-105 active:scale-95 transition-all duration-300"
                            >
                                Sign Up
                            </Link>
                        </>
                    )}
                </nav>
            </div>
        </header>
    );
}
