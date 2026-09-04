import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import EventsList from './pages/EventsList.jsx';
import EventDetail from './pages/EventDetail.jsx';

export default function App() {
    return (
        <BrowserRouter basename="/react">
            <Routes>
                <Route path="/" element={<EventsList />} />
                <Route path="/events/:id" element={<EventDetail />} />
            </Routes>
        </BrowserRouter>
    );
}
