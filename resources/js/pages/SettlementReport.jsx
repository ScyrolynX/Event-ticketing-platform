import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function SettlementReport() {
    const [report, setReport] = useState(null);
    const [error, setError] = useState(null);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }

        fetch('/api/v1/manage/settlement-report', {
            headers: { Authorization: 'Bearer ' + token, Accept: 'application/json' },
        }).then(async (res) => {
            if (res.status === 403) {
                setError('Only Admins can view this report.');
                return;
            }
            const data = await res.json();
            setReport(data.report);
        });
    }, []);

    if (error) {
        return <div className="max-w-4xl mx-auto px-6 py-12 text-red-400">{error}</div>;
    }

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <header className="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
                <div className="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span className="text-lg font-bold text-white">ScyrolynX Staff</span>
                    <Link to="/staff" className="text-sm text-slate-400 hover:text-violet-400">← Dashboard</Link>
                </div>
            </header>

            <main className="max-w-4xl mx-auto px-6 py-12">
                <h1 className="text-2xl font-bold mb-8">Settlement Report</h1>
                <p className="text-slate-400 text-sm mb-8">Amount owed to each promoter after the company's commission.</p>

                {!report ? (
                    <p className="text-slate-400">Loading...</p>
                ) : report.length === 0 ? (
                    <p className="text-slate-400">No promoters yet.</p>
                ) : (
                    <div className="space-y-3">
                        {report.map((r) => (
                            <div key={r.promoter_id} className="bg-slate-900 border border-slate-800 rounded-xl p-5">
                                <div className="font-semibold text-white mb-2">{r.name}</div>
                                <div className="grid grid-cols-2 gap-2 text-sm text-slate-300">
                                    <div>Tickets sold: <span className="text-white font-medium">{r.tickets_sold}</span></div>
                                    <div>Commission rate: <span className="text-white font-medium">{r.commission_rate}%</span></div>
                                    <div>Gross revenue: <span className="text-white font-medium">GHS {Number(r.gross_revenue).toFixed(2)}</span></div>
                                    <div>Commission kept: <span className="text-white font-medium">GHS {Number(r.commission_kept).toFixed(2)}</span></div>
                                </div>
                                <div className="mt-3 pt-3 border-t border-slate-800 text-violet-300 font-bold">
                                    Amount owed to promoter: GHS {Number(r.amount_owed).toFixed(2)}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </main>
        </div>
    );
}
