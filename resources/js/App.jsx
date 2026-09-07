import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import EventsList from './pages/EventsList.jsx';
import EventDetail from './pages/EventDetail.jsx';
import Login from './pages/Login.jsx';
import Register from './pages/Register.jsx';
import MyTickets from './pages/MyTickets.jsx';
import StaffDashboard from './pages/StaffDashboard.jsx';

export default function App() {
    return (
        <BrowserRouter basename="/react">
            <Routes>
                <Route path="/" element={<EventsList />} />
                <Route path="/events/:id" element={<EventDetail />} />
                <Route path="/login" element={<Login />} />
                <Route path="/register" element={<Register />} />
                <Route path="/my-tickets" element={<MyTickets />} />
                <Route path="/staff" element={<StaffDashboard />} />
            </Routes>
        </BrowserRouter>
    );
}
