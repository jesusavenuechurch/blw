import { useMemo, useState } from 'react';
import { Link, usePoll } from '@inertiajs/react';
import { DownloadIcon, HeartHandshakeIcon, ScanLineIcon, SearchIcon, UsersRoundIcon } from 'lucide-react';
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { format } from 'date-fns';
import WorkspaceLayout from '@/layouts/workspace-layout';
import { PartnerAvatar } from '@/components/partner-avatar';
import { TierBadge } from '@/components/tier';
import { LiveDot } from '@/components/workspace/live-dot';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { pct } from '@/lib/format';

interface Props {
    stats: { total: number; today: number; unique_today: number; with_spouse: number; expected: number; arrived: number };
    week: { date: string; label: string; count: number }[];
    feed: {
        id: number;
        checked_in_at: string;
        device: string | null;
        partner: { id: number; title: string | null; full_name: string; tier: string; coming_with_spouse: boolean; spouse_name: string | null };
    }[];
    range: { start: string; end: string };
}

export default function Checkins({ stats, week, feed, range }: Props) {
    usePoll(8000, { only: ['stats', 'week', 'feed'] });
    const [q, setQ] = useState('');
    const [start, setStart] = useState(range.start);
    const [end, setEnd] = useState(range.end);

    const groups = useMemo(() => {
        const needle = q.trim().toLowerCase();
        const items = needle ? feed.filter((f) => f.partner.full_name.toLowerCase().includes(needle)) : feed;
        const byDay = new Map<string, typeof feed>();
        items.forEach((f) => {
            const k = format(new Date(f.checked_in_at), 'yyyy-MM-dd');
            byDay.set(k, [...(byDay.get(k) ?? []), f]);
        });
        return [...byDay.entries()];
    }, [feed, q]);

    const arrivedPct = pct(stats.arrived, stats.expected);

    return (
        <WorkspaceLayout
            title="Check-ins"
            actions={
                <Popover>
                    <PopoverTrigger asChild>
                        <Button variant="outline" size="sm">
                            <DownloadIcon /> Report
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent align="end" className="w-80">
                        <p className="font-medium">Download check-in report</p>
                        <p className="text-muted-foreground mt-1 text-xs">An Excel sheet with every arrival, a tier breakdown and spouse count.</p>
                        <div className="mt-4 grid grid-cols-2 gap-3">
                            <div className="grid gap-1.5">
                                <Label htmlFor="start" className="text-xs">
                                    From
                                </Label>
                                <Input id="start" type="date" value={start} onChange={(e) => setStart(e.target.value)} className="h-9" />
                            </div>
                            <div className="grid gap-1.5">
                                <Label htmlFor="end" className="text-xs">
                                    To
                                </Label>
                                <Input id="end" type="date" value={end} min={start} onChange={(e) => setEnd(e.target.value)} className="h-9" />
                            </div>
                        </div>
                        <Button asChild variant="gold" className="mt-4 w-full">
                            <a href={`/workspace/check-ins/report?start=${start}&end=${end}`}>
                                <DownloadIcon /> Download .xlsx
                            </a>
                        </Button>
                    </PopoverContent>
                </Popover>
            }
        >
            <div className="grid gap-6 lg:grid-cols-[1fr_1.4fr]">
                <div className="space-y-6">
                    {/* Arrival gauge */}
                    <Card className="relative overflow-hidden">
                        <div className="bg-glow pointer-events-none absolute inset-0 opacity-70" />
                        <CardHeader className="relative">
                            <CardDescription className="flex items-center gap-2">
                                <LiveDot /> Live from the lounge scanners
                            </CardDescription>
                            <CardTitle className="font-serif text-6xl font-normal tracking-tight tabular-nums">{stats.today}</CardTitle>
                            <p className="text-muted-foreground text-sm">arrivals today · {stats.unique_today} unique partners</p>
                        </CardHeader>
                        <CardContent className="relative">
                            <div className="flex items-baseline justify-between text-sm">
                                <span className="font-medium">
                                    {stats.arrived} of {stats.expected} registered partners have arrived
                                </span>
                                <span className="text-muted-foreground tabular-nums">{arrivedPct}%</span>
                            </div>
                            <div className="bg-muted mt-2 h-2 overflow-hidden rounded-full">
                                <div className="bg-gold h-full rounded-full transition-all duration-700" style={{ width: `${arrivedPct}%` }} />
                            </div>
                        </CardContent>
                    </Card>

                    <div className="grid grid-cols-2 gap-3">
                        <div className="bg-card rounded-xl border p-4 shadow-xs">
                            <ScanLineIcon className="text-primary size-4" />
                            <p className="mt-3 text-2xl font-semibold tabular-nums">{stats.total.toLocaleString()}</p>
                            <p className="text-muted-foreground text-xs">Check-ins since the event opened</p>
                        </div>
                        <div className="bg-card rounded-xl border p-4 shadow-xs">
                            <HeartHandshakeIcon className="text-primary size-4" />
                            <p className="mt-3 text-2xl font-semibold tabular-nums">{stats.with_spouse.toLocaleString()}</p>
                            <p className="text-muted-foreground text-xs">Check-ins with a spouse</p>
                        </div>
                    </div>

                    <Card className="gap-2">
                        <CardHeader>
                            <CardTitle className="text-base">Last 7 days</CardTitle>
                        </CardHeader>
                        <CardContent className="h-56 px-2">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={week} margin={{ top: 8, right: 16, left: -8, bottom: 0 }}>
                                    <CartesianGrid vertical={false} stroke="var(--border)" />
                                    <XAxis dataKey="label" tickLine={false} axisLine={false} tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }} />
                                    <YAxis allowDecimals={false} tickLine={false} axisLine={false} tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }} width={36} />
                                    <Tooltip
                                        cursor={{ fill: 'var(--accent)' }}
                                        contentStyle={{ background: 'var(--popover)', border: '1px solid var(--border)', borderRadius: 8, fontSize: 12 }}
                                        formatter={(v) => [v, 'Check-ins']}
                                        labelFormatter={(_, p) => (p?.[0] ? format(new Date(p[0].payload.date), 'EEEE, MMM d') : '')}
                                    />
                                    <Bar dataKey="count" radius={[6, 6, 2, 2]} fill="var(--gold-2)" />
                                </BarChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                </div>

                {/* Arrivals feed */}
                <Card className="min-h-0 gap-0 py-0">
                    <div className="flex items-center gap-3 border-b p-4">
                        <h3 className="font-semibold tracking-tight">Arrivals</h3>
                        <div className="relative ml-auto w-56">
                            <SearchIcon className="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                            <Input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Find a guest…" className="h-8 pl-9" />
                        </div>
                    </div>
                    <div className="scrollbar-thin max-h-[calc(100svh-12rem)] overflow-y-auto">
                        {groups.length === 0 ? (
                            <div className="text-muted-foreground py-20 text-center text-sm">
                                <UsersRoundIcon className="mx-auto mb-3 size-5" />
                                {q ? 'No arrivals match that name.' : 'No arrivals yet. They’ll stream in here as ushers scan passes.'}
                            </div>
                        ) : (
                            groups.map(([d, items]) => (
                                <section key={d}>
                                    <h4 className="bg-muted/60 text-muted-foreground sticky top-0 z-10 flex justify-between px-4 py-1.5 text-xs font-medium backdrop-blur">
                                        <span>{format(new Date(d + 'T00:00:00'), 'EEEE, MMMM d')}</span>
                                        <span className="tabular-nums">{items.length}</span>
                                    </h4>
                                    {items.map((f) => (
                                        <Link
                                            key={f.id}
                                            href={`/workspace/people?partner=${f.partner.id}`}
                                            className="hover:bg-accent/40 flex items-center gap-3 border-b px-4 py-3 last:border-0"
                                        >
                                            <PartnerAvatar name={f.partner.full_name} tier={f.partner.tier} />
                                            <div className="min-w-0 flex-1">
                                                <p className="truncate text-sm font-medium">
                                                    {f.partner.title} {f.partner.full_name}
                                                </p>
                                                <p className="text-muted-foreground truncate text-xs">
                                                    {[f.partner.coming_with_spouse ? `with ${f.partner.spouse_name || 'spouse'}` : null, f.device].filter(Boolean).join(' · ')}
                                                </p>
                                            </div>
                                            <TierBadge tier={f.partner.tier} short className="hidden sm:inline-flex" />
                                            <span className="text-muted-foreground w-16 text-right text-xs tabular-nums">{format(new Date(f.checked_in_at), 'h:mm a')}</span>
                                        </Link>
                                    ))}
                                </section>
                            ))
                        )}
                    </div>
                </Card>
            </div>
        </WorkspaceLayout>
    );
}
