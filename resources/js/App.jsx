import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import EventsList from './pages/EventsList.jsx';

export default function App() {
    return (
        <BrowserRouter basename="/react">
            <Routes>
                <Route path="/" element={<EventsList />} />
            </Routes>
        </BrowserRouter>
    );
}
