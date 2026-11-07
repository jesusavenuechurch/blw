import { useEffect, useState, type FormEvent } from 'react';
import { router, useForm, usePoll } from '@inertiajs/react';
import QRCode from 'qrcode';
import { CheckCircle2Icon, CopyIcon, Loader2Icon, MoreHorizontalIcon, PlusIcon, RefreshCwIcon, ScanLineIcon, ShieldOffIcon, SmartphoneIcon } from 'lucide-react';
import { toast } from 'sonner';
import WorkspaceLayout from '@/layouts/workspace-layout';
import { Field } from '@/components/field';
import { LiveDot } from '@/components/workspace/live-dot';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ago } from '@/lib/format';

interface Device {
    id: number;
    name: string;
    status: 'active' | 'waiting' | 'expired' | 'revoked';
    platform: string | null;
    app_version: string | null;
    paired_at: string | null;
    last_seen_at: string | null;
    check_ins: number;
    today: number;
}

interface Pairing {
    id: number;
    name: string;
    code: string;
    expires_at: string;
    qr: string;
    api: string;
}

const STATUS: Record<Device['status'], { label: string; variant: 'success' | 'warning' | 'secondary' | 'destructive' }> = {
    active: { label: 'Paired', variant: 'success' },
    waiting: { label: 'Waiting to pair', variant: 'warning' },
    expired: { label: 'Code expired', variant: 'secondary' },
    revoked: { label: 'Revoked', variant: 'destructive' },
};

export default function Scanners({ devices, pairing }: { devices: Device[]; pairing: Pairing | null }) {
    usePoll(4000, { only: ['devices'] });
    const [adding, setAdding] = useState(false);
    const [showing, setShowing] = useState<Pairing | null>(pairing);

    useEffect(() => setShowing(pairing), [pairing]);

    // Close the pairing dialog the moment the phone finishes pairing.
    const paired = showing ? devices.find((d) => d.id === showing.id && d.status === 'active') : undefined;
    useEffect(() => {
        if (paired) {
            toast.success(`${paired.name} is paired and ready to scan.`);
            setShowing(null);
        }
    }, [paired]);

    const online = devices.filter((d) => d.status === 'active' && d.last_seen_at && Date.now() - new Date(d.last_seen_at).getTime() < 3 * 60_000).length;

    return (
        <WorkspaceLayout
            title="Scanners"
            actions={
                <Button size="sm" variant="outline" onClick={() => setAdding(true)}>
                    <PlusIcon /> Pair a scanner
                </Button>
            }
        >
            <div className="mx-auto max-w-4xl">
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 className="font-serif text-3xl tracking-tight">Lounge scanners</h2>
                        <p className="text-muted-foreground mt-1 text-sm">Phones running the Angel Lounges scanner. They work offline and sync whenever they have signal.</p>
                    </div>
                    {online > 0 && (
                        <span className="text-muted-foreground flex items-center gap-2 text-sm">
                            <LiveDot /> {online} online now
                        </span>
                    )}
                </div>

                {devices.length === 0 ? (
                    <div className="mt-8 flex flex-col items-center rounded-2xl border border-dashed px-6 py-16 text-center">
                        <span className="bg-gold grid size-14 place-items-center rounded-2xl text-[oklch(0.22_0.04_65)] shadow-sm">
                            <SmartphoneIcon className="size-6" />
                        </span>
                        <h3 className="mt-5 font-serif text-2xl">No scanners yet</h3>
                        <p className="text-muted-foreground mt-2 max-w-sm text-sm">Install the scanner app on an usher’s phone, then pair it here by scanning a code.</p>
                        <Button variant="gold" className="mt-6" onClick={() => setAdding(true)}>
                            <PlusIcon /> Pair the first scanner
                        </Button>
                    </div>
                ) : (
                    <Card className="mt-6 gap-0 py-0">
                        {devices.map((d) => (
                            <div key={d.id} className="flex items-center gap-4 border-b px-5 py-4 last:border-0">
                                <span className="bg-muted grid size-10 shrink-0 place-items-center rounded-xl">
                                    <SmartphoneIcon className="text-muted-foreground size-5" />
                                </span>
                                <div className="min-w-0 flex-1">
                                    <p className="flex items-center gap-2 truncate font-medium">
                                        {d.name}
                                        <Badge variant={STATUS[d.status].variant}>{STATUS[d.status].label}</Badge>
                                    </p>
                                    <p className="text-muted-foreground truncate text-xs">
                                        {d.status === 'active'
                                            ? `Last seen ${ago(d.last_seen_at)}${d.platform ? ` · ${d.platform}` : ''}${d.app_version ? ` · v${d.app_version}` : ''}`
                                            : d.status === 'revoked'
                                              ? 'Can no longer sync or scan'
                                              : 'Not paired yet'}
                                    </p>
                                </div>
                                <div className="hidden text-right sm:block">
                                    <p className="text-sm font-semibold tabular-nums">{d.today}</p>
                                    <p className="text-muted-foreground text-xs">today</p>
                                </div>
                                <div className="hidden w-16 text-right sm:block">
                                    <p className="text-sm font-semibold tabular-nums">{d.check_ins}</p>
                                    <p className="text-muted-foreground text-xs">total</p>
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="ghost" size="icon-sm" aria-label={`Actions for ${d.name}`}>
                                            <MoreHorizontalIcon />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        {(d.status === 'waiting' || d.status === 'expired') && (
                                            <DropdownMenuItem onSelect={() => router.post(`/workspace/scanners/${d.id}/renew`, {}, { preserveScroll: true })}>
                                                <RefreshCwIcon /> Show a new pairing code
                                            </DropdownMenuItem>
                                        )}
                                        {d.status !== 'revoked' && (
                                            <DropdownMenuItem
                                                variant="destructive"
                                                onSelect={() => router.delete(`/workspace/scanners/${d.id}`, { preserveScroll: true })}
                                            >
                                                <ShieldOffIcon /> {d.status === 'active' ? 'Revoke access' : 'Cancel pairing'}
                                            </DropdownMenuItem>
                                        )}
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        ))}
                    </Card>
                )}

                <div className="text-muted-foreground mt-6 flex gap-3 rounded-xl border p-4 text-sm">
                    <ScanLineIcon className="text-primary mt-0.5 size-4 shrink-0" />
                    <p>
                        Lost a phone? Revoke it here and it stops syncing straight away. Check-ins it made offline and never uploaded stay on that phone.
                    </p>
                </div>
            </div>

            <AddDialog open={adding} onClose={() => setAdding(false)} />
            {showing && <PairingDialog pairing={showing} onClose={() => setShowing(null)} />}
        </WorkspaceLayout>
    );
}

function AddDialog({ open, onClose }: { open: boolean; onClose: () => void }) {
    const { data, setData, post, processing, errors, reset } = useForm({ name: '' });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/workspace/scanners', {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    }

    return (
        <Dialog open={open} onOpenChange={(o) => !o && onClose()}>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-5">
                    <DialogHeader>
                        <DialogTitle>Pair a scanner</DialogTitle>
                        <DialogDescription>Give this phone a name you’ll recognise, like the door it will stand at.</DialogDescription>
                    </DialogHeader>
                    <Field label="Scanner name" required error={errors.name}>
                        <Input value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="Main entrance" autoFocus />
                    </Field>
                    <DialogFooter>
                        <Button type="button" variant="ghost" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" variant="gold" disabled={processing}>
                            {processing && <Loader2Icon className="animate-spin" />} Create pairing code
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function PairingDialog({ pairing, onClose }: { pairing: Pairing; onClose: () => void }) {
    const [qr, setQr] = useState<string | null>(null);
    const [left, setLeft] = useState(() => Math.max(0, new Date(pairing.expires_at).getTime() - Date.now()));

    useEffect(() => {
        QRCode.toDataURL(pairing.qr, { width: 560, margin: 1, errorCorrectionLevel: 'M', color: { dark: '#2a2016', light: '#ffffff' } }).then(setQr);
    }, [pairing.qr]);

    useEffect(() => {
        const t = window.setInterval(() => setLeft(Math.max(0, new Date(pairing.expires_at).getTime() - Date.now())), 1000);
        return () => window.clearInterval(t);
    }, [pairing.expires_at]);

    const mins = Math.floor(left / 60000);
    const secs = Math.floor((left % 60000) / 1000);

    return (
        <Dialog open onOpenChange={(o) => !o && onClose()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Pair “{pairing.name}”</DialogTitle>
                    <DialogDescription>Open the scanner app on the phone and point it at this code.</DialogDescription>
                </DialogHeader>
                <div className="flex flex-col items-center">
                    <div className="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-black/5">
                        {qr ? <img src={qr} alt="Pairing code" className="size-60" /> : <div className="grid size-60 place-items-center"><Loader2Icon className="animate-spin" /></div>}
                    </div>
                    <p className="text-muted-foreground mt-4 text-xs">Or type this code in the app</p>
                    <button
                        type="button"
                        onClick={() => navigator.clipboard?.writeText(pairing.code).then(() => toast.success('Code copied'), () => {})}
                        className="hover:bg-accent mt-1 flex items-center gap-2 rounded-lg px-3 py-1 font-mono text-3xl font-semibold tracking-[0.3em]"
                    >
                        {pairing.code}
                        <CopyIcon className="text-muted-foreground size-4" />
                    </button>
                    <p className="text-muted-foreground mt-1 text-xs">
                        with server <span className="font-mono">{pairing.api}</span>
                    </p>
                    <p className="mt-4 flex items-center gap-2 text-sm">
                        {left > 0 ? (
                            <>
                                <Loader2Icon className="text-primary size-4 animate-spin" /> Waiting for the phone · expires in {mins}:{String(secs).padStart(2, '0')}
                            </>
                        ) : (
                            <span className="text-destructive">This code has expired. Create a new one.</span>
                        )}
                    </p>
                </div>
                <DialogFooter>
                    <Button variant="ghost" onClick={onClose}>
                        <CheckCircle2Icon /> Done
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
