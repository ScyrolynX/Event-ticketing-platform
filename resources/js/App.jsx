import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import EventsList from './pages/EventsList.jsx';
import EventDetail from './pages/EventDetail.jsx';
import Login from './pages/Login.jsx';
import Register from './pages/Register.jsx';
import MyTickets from './pages/MyTickets.jsx';
import Checkout from './pages/Checkout.jsx';
import StaffDashboard from './pages/StaffDashboard.jsx';
import CheckIn from './pages/CheckIn.jsx';
import ManageEvents from './pages/ManageEvents.jsx';
import ManageStaff from './pages/ManageStaff.jsx';
import ManagePromoters from './pages/ManagePromoters.jsx';
import ManageDiscounts from './pages/ManageDiscounts.jsx';
import ManageRefunds from './pages/ManageRefunds.jsx';
import SalesDashboard from './pages/SalesDashboard.jsx';
import SettlementReport from './pages/SettlementReport.jsx';

export default function App() {
    return (
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<EventsList />} />
                <Route path="/events/:id" element={<EventDetail />} />
                <Route path="/login" element={<Login />} />
                <Route path="/register" element={<Register />} />
                <Route path="/my-tickets" element={<MyTickets />} />
                <Route path="/checkout/:id" element={<Checkout />} />
                <Route path="/staff" element={<StaffDashboard />} />
                <Route path="/check-in" element={<CheckIn />} />
                <Route path="/manage/events" element={<ManageEvents />} />
                <Route path="/manage/staff" element={<ManageStaff />} />
                <Route path="/manage/promoters" element={<ManagePromoters />} />
                <Route path="/manage/discounts" element={<ManageDiscounts />} />
                <Route path="/manage/refunds" element={<ManageRefunds />} />
                <Route path="/manage/dashboard" element={<SalesDashboard />} />
                <Route path="/manage/settlement-report" element={<SettlementReport />} />
            </Routes>
        </BrowserRouter>
    );
}
