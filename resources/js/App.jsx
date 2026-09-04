import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';

function Home() {
    return (
        <div style={{ padding: '3rem', color: 'white', fontFamily: 'sans-serif' }}>
            <h1>React is working 🎉</h1>
            <p>This is being rendered by React, inside your Laravel app.</p>
        </div>
    );
}

export default function App() {
    return (
        <BrowserRouter basename="/react">
            <Routes>
                <Route path="/" element={<Home />} />
            </Routes>
        </BrowserRouter>
    );
}
