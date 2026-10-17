import React, { useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';

export default function CheckIn() {
    const [code, setCode] = useState('');
    const [result, setResult] = useState(null);
    const [scanning, setScanning] = useState(false);
    const [scanError, setScanError] = useState(null);
    const scannerRef = useRef(null);
    const hasScannedRef = useRef(false);
    const navigate = useNavigate();
    const token = localStorage.getItem('token');

    if (!token) {
        navigate('/login');
        return null;
    }

    // Runs the actual camera/scan lifecycle. Mounting and tearing down the
    // scanner lives here (not in the button handlers) so the "stop the
    // camera" cleanup always runs — on a successful scan, on Cancel, and if
    // the staff member navigates away mid-scan — instead of being
    // duplicated in three places.
    useEffect(() => {
        if (!scanning) return;

        hasScannedRef.current = false;
        let cancelled = false;

        (async () => {
            const { Html5Qrcode } = await import('html5-qrcode');
            if (cancelled) return;

            const scanner = new Html5Qrcode('qr-reader');
            scannerRef.current = scanner;

            try {
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 240, height: 240 } },
                    (decodedText) => {
                        // The callback can fire a few more times while the
                        // camera is winding down — only act on the first hit.
                        if (hasScannedRef.current) return;
                        hasScannedRef.current = true;
                        setScanning(false);
                        submitCode(decodedText);
                    },
                    () => {} // per-frame "no QR in view yet" noise — ignored
                );
            } catch (err) {
                if (!cancelled) {
                    setScanError("Could not access the camera. Check that you've allowed camera access for this site, or enter the code manually below.");
                    setScanning(false);
                }
            }
        })();

        return () => {
            cancelled = true;
            if (scannerRef.current) {
                const s = scannerRef.current;
                scannerRef.current = null;
                s.stop().then(() => s.clear()).catch(() => {});
            }
        };
    }, [scanning]);

    async function submitCode(rawCode) {
        const res = await fetch('/api/v1/check-in', {
            method: 'POST',
            headers: {
                Authorization: 'Bearer ' + token,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({ unique_code: rawCode.trim() }),
        });

        const data = await res.json();

        if (res.status === 403) {
            setResult({ type: 'error', icon: '🚫', title: 'Not authorized', detail: 'Your account does not have check-in access.' });
        } else if (res.ok) {
            setResult({ type: 'success', icon: '✅', title: 'Ticket accepted', detail: `Ticket #${data.ticket_id}, admit guest.` });
        } else {
            setResult({ type: 'warn', icon: '⚠️', title: data.message || 'Rejected', detail: null });
        }
    }

    async function handleManualSubmit(e) {
        e.preventDefault();
        await submitCode(code);
        setCode('');
    }

    function startScanning() {
        setResult(null);
        setScanError(null);
        setScanning(true);
    }

    function cancelScanning() {
        setScanning(false);
    }

    const resultClasses = {
        success: 'bg-green-500/10 border-green-500/40 text-green-300',
        error: 'bg-red-500/10 border-red-500/40 text-red-300',
        warn: 'bg-amber-500/10 border-amber-500/40 text-amber-300',
    };

    return (
        <div className="min-h-screen bg-stone-950 text-stone-100">
            <header className="bg-stone-950/80 backdrop-blur border-b border-brand-600/25 sticky top-0 z-10">
                <div className="max-w-2xl mx-auto px-6 py-5 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className="w-8 h-8 bg-gradient-to-br from-brand-600 to-gold-500 rounded-md flex items-center justify-center shadow-lg shadow-brand-600/40">
                            <span className="text-white font-bold text-sm">S</span>
                        </div>
                        <span className="text-lg font-bold text-white">ScyrolynX Staff</span>
                    </div>
                    <Link to="/" className="text-sm text-stone-400 hover:text-gold-400">← Exit</Link>
                </div>
            </header>

            <main className="max-w-md mx-auto px-6 py-16 text-center">
                <h1 className="text-2xl font-bold mb-2">Ticket Check-In</h1>
                <p className="text-stone-400 text-sm mb-8">Scan a ticket's QR code with your camera, or type its code in by hand.</p>

                {result && (
                    <div className={`rounded-2xl p-8 mb-6 border ${resultClasses[result.type]}`}>
                        <div className="text-4xl mb-2">{result.icon}</div>
                        <div className="font-semibold">{result.title}</div>
                        {result.detail && <div className="text-sm mt-1">{result.detail}</div>}
                    </div>
                )}

                {scanError && !scanning && (
                    <p className="text-amber-300 text-xs mb-4 bg-amber-500/10 border border-amber-500/30 rounded-lg px-4 py-3">{scanError}</p>
                )}

                {/* Always mounted (just hidden) so the scanner library has a
                    real element to attach to the instant it's told to start —
                    no race between "camera turned on" and "div exists". */}
                <div
                    id="qr-reader"
                    className={scanning ? 'w-full rounded-2xl overflow-hidden border border-gold-500/30 mb-6' : 'hidden'}
                />

                {scanning ? (
                    <button
                        onClick={cancelScanning}
                        className="w-full bg-stone-800 hover:bg-stone-700 text-stone-200 font-medium py-3 rounded-full transition-all duration-300 mb-8"
                    >
                        Cancel Scan
                    </button>
                ) : (
                    <button
                        onClick={startScanning}
                        className="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-brand-600 to-gold-600 hover:from-brand-500 hover:to-gold-500 text-white font-semibold py-3.5 rounded-full transition-all duration-300 hover:scale-[1.02] active:scale-95 shadow-lg shadow-gold-500/20 mb-8"
                    >
                        📷 Scan with Camera
                    </button>
                )}

                {!scanning && (
                    <>
                        <div className="flex items-center gap-3 mb-6">
                            <div className="h-px bg-stone-800 flex-1" />
                            <span className="text-xs text-stone-500 uppercase tracking-wide">or enter manually</span>
                            <div className="h-px bg-stone-800 flex-1" />
                        </div>

                        <form onSubmit={handleManualSubmit} className="space-y-4">
                            <textarea
                                value={code}
                                onChange={(e) => setCode(e.target.value)}
                                rows={3}
                                placeholder="Ticket code..."
                                required
                                className="w-full bg-stone-900 border border-stone-700 rounded-md px-3 py-2 text-stone-100 text-xs focus:outline-none focus:border-gold-500"
                            />
                            <button
                                type="submit"
                                className="w-full bg-gold-500 hover:bg-gold-400 text-stone-950 font-medium py-3 rounded-full transition-all duration-300 active:scale-95"
                            >
                                Check In
                            </button>
                        </form>
                    </>
                )}
            </main>
        </div>
    );
}
