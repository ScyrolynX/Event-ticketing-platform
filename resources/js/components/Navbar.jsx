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
        <header className="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
            <div className="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
                <Link to="/" className="flex items-center gap-2">
                    <div className="w-8 h-8 bg-gradient-to-br from-violet-500 to-blue-500 rounded-md flex items-center justify-center shadow-lg shadow-violet-500/30">
                        <span className="text-white font-bold text-sm">S</span>
                    </div>
                    <span className="text-lg font-bold text-white">ScyrolynX</span>
                </Link>

                <nav className="flex items-center gap-5 text-sm">
                    <Link to="/" className="text-slate-400 hover:text-violet-400">Events</Link>

                    {token ? (
                        <>
                            <Link to="/my-tickets" className="text-slate-400 hover:text-violet-400">My Tickets</Link>
                            {roles.length > 0 && (
                                <Link to="/staff" className="text-slate-400 hover:text-violet-400">Staff</Link>
                            )}
                            <button onClick={handleLogout} className="text-slate-400 hover:text-red-400">Logout</button>
                        </>
                    ) : (
                        <>
                            <Link to="/login" className="text-slate-400 hover:text-violet-400">Login</Link>
                            <Link
                                to="/register"
                                className="bg-violet-600 hover:bg-violet-500 text-white px-4 py-1.5 rounded-full font-medium"
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
