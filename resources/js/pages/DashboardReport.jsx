import React, { useEffect, useState } from 'react';

export default function DashboardReport() {
    const token = localStorage.getItem('token');
    const [tab, setTab] = useState('sales');
    const [sales, setSales] = useState(null);
    const [settlement, setSettlement] = useState(null);

    useEffect(() => {
        fetch('/api/v1/manage/dashboard', { headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' } })
            .then((res) => res.json())
            .then(setSales);

        fetch('/api/v1/manage/settlement-report', { headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' } })
            .then((res) => res.json())
            .then((data) => setSettlement(data.report || []));
    }, []);

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <header className="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
                <div className="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX Staff</span>
                    <a href="/react/staff" className="text-sm text-slate-400 hover:text-violet-400">← Dashboard</a>
                </div>
            </header>

            <main className="max-w-4xl mx-auto px-6 py-12">
                <div className="flex gap-2 mb-8">
                    <button onClick={() => setTab('sales')} className={`px-4 py-2 rounded-full text-sm ${tab === 'sales' ? 'bg-violet-600 text-white' : 'bg-slate-800 text-slate-300'}`}>Sales Dashboard</button>
                    <button onClick={() => setTab('settlement')} className={`px-4 py-2 rounded-full text-sm ${tab === 'settlement' ? 'bg-violet-600 text-white' : 'bg-slate-800 text-slate-300'}`}>Settlement Report</button>
                </div>

                {tab === 'sales' && sales && (
                    <>
                        <div className="grid grid-cols-2 gap-4 mb-10">
                            <div className="bg-slate-900 border border-slate-800 rounded-xl p-6">
                                <div className="text-3xl font-bold text-white">{Number(sales.total_revenue).toFixed(2)}</div>
                                <div className="text-sm text-slate-400">Total Revenue (GHS)</div>
                            </div>
                            <div className="bg-slate-900 border border-slate-800 rounded-xl p-6">
                                <div className="text-3xl font-bold text-white">{sales.total_tickets_sold}</div>
                                <div className="text-sm text-slate-400">Tickets Sold</div>
                            </div>
                        </div>
                        <div className="space-y-3">
                            {sales.events.map((e) => (
                                <div key={e.event_id} className="bg-slate-900 border border-slate-800 rounded-lg px-4 py-3 flex justify-between items-center">
                                    <div className="text-sm font-medium text-white">{e.title}</div>
                                    <div className="text-xs text-slate-400">{e.tickets_sold} sold · {e.tickets_remaining} remaining · GHS {Number(e.revenue).toFixed(2)}</div>
                                </div>
                            ))}
                        </div>
                    </>
                )}

                {tab === 'settlement' && settlement && (
                    <div className="space-y-3">
                        {settlement.length === 0 && <p className="text-slate-400">No promoters yet.</p>}
                        {settlement.map((r) => (
                            <div key={r.promoter_id} className="bg-slate-900 border border-slate-800 rounded-xl p-5">
                                <div className="font-semibold text-white mb-2">{r.name}</div>
                                <div className="grid grid-cols-2 gap-2 text-sm text-slate-300">
                                    <div>Tickets sold: <span className="text-white font-medium">{r.tickets_sold}</span></div>
                                    <div>Commission rate: <span className="text-white font-medium">{r.commission_rate}%</span></div>
                                    <div>Gross revenue: <span className="text-white font-medium">GHS {Number(r.gross_revenue).toFixed(2)}</span></div>
                                    <div>Commission kept: <span className="text-white font-medium">GHS {Number(r.commission_kept).toFixed(2)}</span></div>
                                </div>
                                <div className="mt-3 pt-3 border-t border-slate-800 text-violet-300 font-bold">
                                    Amount owed: GHS {Number(r.amount_owed).toFixed(2)}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </main>
        </div>
    );
}
