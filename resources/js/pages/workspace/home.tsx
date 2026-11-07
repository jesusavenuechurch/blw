import { Link, usePage, usePoll } from '@inertiajs/react';
import {
    AlertTriangleIcon,
    ArrowRightIcon,
    BadgeCheckIcon,
    BookOpenIcon,
    CheckCircle2Icon,
    ClockIcon,
    HeartHandshakeIcon,
    MailCheckIcon,
    PlaneIcon,
    ScanLineIcon,
    SendIcon,
    SofaIcon,
} from 'lucide-react';
import { Bar, BarChart, ResponsiveContainer, Tooltip, XAxis } from 'recharts';
import WorkspaceLayout from '@/layouts/workspace-layout';
import { PartnerAvatar } from '@/components/partner-avatar';
import { LiveDot } from '@/components/workspace/live-dot';
import { TierDot } from '@/components/tier';
import { Card } from '@/components/ui/card';
import { ago, greeting, pct } from '@/lib/format';
import { TIER_META } from '@/lib/tiers';
import { cn } from '@/lib/utils';
import type { SharedProps, Tier } from '@/types';

interface Props {
    pulse: {
        total: number;
        funnel: { invited: number; delivered: number; registered: number; checked_in: number };
        awaiting: number;
        email: { failed: number; pending: number; sent: number };
        attending_ippc: number;
        lounge_guests: number;
        with_spouse: number;
        ror_copies: number;
        today_check_ins: number;
        tiers: { tier: Tier; total: number; registered: number }[];
    };
    activity: {
        key: string;
        type: 'registered' | 'checked_in';
        partner: { id: number; title: string | null; full_name: string; tier: string };
        at: string;
    }[];
    week: { date: string; label: string; count: number }[];
}

export default function Home({ pulse, activity, week }: Props) {
    const { auth } = usePage<SharedProps>().props;
    usePoll(15000, { only: ['pulse', 'activity', 'week'] });

    const journey = [
        { key: 'invited', label: 'On the list', value: pulse.funnel.invited, icon: SendIcon, href: '/workspace/people' },
        { key: 'delivered', label: 'Invitation delivered', value: pulse.funnel.delivered, icon: MailCheckIcon, href: '/workspace/people?stage=delivered' },
        { key: 'registered', label: 'Registered', value: pulse.funnel.registered, icon: BadgeCheckIcon, href: '/workspace/people?stage=registered' },
        { key: 'checked_in', label: 'Checked in', value: pulse.funnel.checked_in, icon: ScanLineIcon, href: '/workspace/people?stage=checked_in' },
    ];

    const attention = [
        pulse.email.failed > 0 && {
            tone: 'destructive',
            icon: AlertTriangleIcon,
            title: `${pulse.email.failed} invitation${pulse.email.failed === 1 ? '' : 's'} bounced`,
            text: 'Fix the address or resend from the partner profile.',
            href: '/workspace/people?view=email_failed',
        },
        pulse.awaiting > 0 && {
            tone: 'warning',
            icon: ClockIcon,
            title: `${pulse.awaiting} partner${pulse.awaiting === 1 ? ' hasn’t' : 's haven’t'} registered yet`,
            text: 'A gentle nudge usually helps — resend their link.',
            href: '/workspace/people?view=awaiting',
        },
        pulse.email.pending > 0 && {
            tone: 'info',
            icon: SendIcon,
            title: `${pulse.email.pending} email${pulse.email.pending === 1 ? '' : 's'} on the way`,
            text: 'Queued and being delivered right now.',
            href: '/workspace/outreach',
        },
    ].filter(Boolean) as { tone: string; icon: typeof ClockIcon; title: string; text: string; href: string }[];

    return (
        <WorkspaceLayout title="Home">
            <div className="animate-rise mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p className="text-muted-foreground flex items-center gap-2 text-sm">
                        <LiveDot /> Live · IPPC 2025
                    </p>
                    <h2 className="mt-2 font-serif text-4xl tracking-tight sm:text-5xl">
                        {greeting()}, <span className="text-gold italic">{auth.user?.name.split(' ')[0]}</span>
                    </h2>
                    <p className="text-muted-foreground mt-2">
                        {pulse.today_check_ins > 0
                            ? `${pulse.today_check_ins} guest${pulse.today_check_ins === 1 ? ' has' : 's have'} arrived at the lounge today.`
                            : 'Here’s how the guest list is shaping up.'}
                    </p>
                </div>
            </div>

            {/* Journey */}
            <Card className="mb-6 gap-0 overflow-hidden p-0">
                <div className="grid sm:grid-cols-2 xl:grid-cols-4">
                    {journey.map((step, i) => {
                        const prev = i === 0 ? step.value : journey[i - 1].value;
                        const conversion = pct(step.value, prev);
                        return (
                            <Link
                                key={step.key}
                                href={step.href}
                                className={cn(
                                    'group hover:bg-accent/40 relative p-6 transition-colors',
                                    i > 0 && 'border-t sm:border-t-0 sm:[&:nth-child(n+3)]:border-t xl:[&:nth-child(n+3)]:border-t-0',
                                    i % 2 === 1 && 'sm:border-l',
                                    i > 0 && 'xl:border-l',
                                )}
                            >
                                <div className="text-muted-foreground flex items-center gap-2 text-sm">
                                    <step.icon className="size-4" />
                                    {step.label}
                                    <ArrowRightIcon className="ml-auto size-4 -translate-x-1 opacity-0 transition-all group-hover:translate-x-0 group-hover:opacity-100" />
                                </div>
                                <p className="mt-3 font-serif text-5xl tracking-tight tabular-nums">{step.value.toLocaleString()}</p>
                                <div className="mt-4 flex items-center gap-3">
                                    <div className="bg-muted h-1.5 flex-1 overflow-hidden rounded-full">
                                        <div
                                            className="bg-gold h-full rounded-full transition-all duration-700"
                                            style={{ width: `${pct(step.value, pulse.total)}%` }}
                                        />
                                    </div>
                                    <span className="text-muted-foreground w-24 text-right text-xs tabular-nums">
                                        {i === 0 ? 'everyone' : `${conversion}% of previous`}
                                    </span>
                                </div>
                            </Link>
                        );
                    })}
                </div>
            </Card>

            <div className="grid gap-6 xl:grid-cols-[1fr_380px]">
                <div className="min-w-0 space-y-6">
                    {/* Attention */}
                    {attention.length > 0 && (
                        <div className="grid gap-3 md:grid-cols-3">
                            {attention.map((a) => (
                                <Link
                                    key={a.title}
                                    href={a.href}
                                    className="bg-card group hover:border-foreground/15 flex gap-3 rounded-xl border p-4 shadow-xs transition-all hover:shadow-sm"
                                >
                                    <span
                                        className={cn(
                                            'grid size-9 shrink-0 place-items-center rounded-lg',
                                            a.tone === 'destructive' && 'bg-destructive/10 text-destructive',
                                            a.tone === 'warning' && 'bg-warning/15 text-[color-mix(in_oklch,var(--warning)_60%,black)] dark:text-warning',
                                            a.tone === 'info' && 'bg-info/10 text-info',
                                        )}
                                    >
                                        <a.icon className="size-4" />
                                    </span>
                                    <span className="min-w-0">
                                        <span className="block text-sm font-medium">{a.title}</span>
                                        <span className="text-muted-foreground block text-xs">{a.text}</span>
                                    </span>
                                </Link>
                            ))}
                        </div>
                    )}

                    {/* Lounges */}
                    <section>
                        <div className="mb-3 flex items-center justify-between">
                            <h3 className="font-semibold tracking-tight">Lounges</h3>
                            <Link href="/workspace/people?group=tier" className="text-muted-foreground hover:text-foreground text-sm">
                                View by lounge →
                            </Link>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            {pulse.tiers.map((t) => {
                                const meta = TIER_META[t.tier];
                                const p = pct(t.registered, t.total);
                                return (
                                    <Link
                                        key={t.tier}
                                        href={`/workspace/people?tier=${t.tier}`}
                                        className="bg-card group relative overflow-hidden rounded-xl border p-5 shadow-xs transition-all hover:-translate-y-0.5 hover:shadow-md"
                                    >
                                        <div
                                            className="pointer-events-none absolute -top-16 -right-16 size-40 rounded-full opacity-30 blur-2xl transition-opacity group-hover:opacity-50"
                                            style={{ background: meta.color }}
                                        />
                                        <div className="relative flex items-center gap-2 text-sm font-medium">
                                            <TierDot tier={t.tier} className="size-2.5" />
                                            {meta.label}
                                        </div>
                                        <div className="relative mt-4 flex items-baseline gap-2">
                                            <span className="font-serif text-4xl tabular-nums">{t.total}</span>
                                            <span className="text-muted-foreground text-sm">partners</span>
                                        </div>
                                        <div className="relative mt-3">
                                            <div className="bg-muted h-1 overflow-hidden rounded-full">
                                                <div className="h-full rounded-full transition-all duration-700" style={{ width: `${p}%`, background: meta.color }} />
                                            </div>
                                            <p className="text-muted-foreground mt-2 text-xs tabular-nums">
                                                {t.registered} registered · {p}%
                                            </p>
                                        </div>
                                    </Link>
                                );
                            })}
                        </div>
                    </section>

                    {/* Numbers */}
                    <section className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                        {[
                            { icon: PlaneIcon, label: 'Attending IPPC', value: pulse.attending_ippc },
                            { icon: SofaIcon, label: 'Lounge guests', value: pulse.lounge_guests, href: '/workspace/people?view=lounge' },
                            { icon: HeartHandshakeIcon, label: 'With spouse', value: pulse.with_spouse, href: '/workspace/people?view=spouse' },
                            { icon: BookOpenIcon, label: 'ROR copies sponsored', value: pulse.ror_copies },
                        ].map((n) => {
                            const body = (
                                <>
                                    <n.icon className="text-primary size-4" />
                                    <p className="mt-3 text-2xl font-semibold tracking-tight tabular-nums">{n.value.toLocaleString()}</p>
                                    <p className="text-muted-foreground text-xs">{n.label}</p>
                                </>
                            );
                            return n.href ? (
                                <Link key={n.label} href={n.href} className="bg-card hover:bg-accent/30 rounded-xl border p-4 shadow-xs transition-colors">
                                    {body}
                                </Link>
                            ) : (
                                <div key={n.label} className="bg-card rounded-xl border p-4 shadow-xs">
                                    {body}
                                </div>
                            );
                        })}
                    </section>
                </div>

                {/* Right rail */}
                <div className="space-y-6">
                    <Card className="gap-0 py-5">
                        <div className="flex items-center justify-between px-5">
                            <h3 className="font-semibold tracking-tight">Arrivals this week</h3>
                            <Link href="/workspace/check-ins" className="text-muted-foreground hover:text-foreground text-sm">
                                Open →
                            </Link>
                        </div>
                        <div className="h-36 px-2 pt-3">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={week} margin={{ top: 8, right: 8, left: 8, bottom: 0 }}>
                                    <XAxis dataKey="label" tickLine={false} axisLine={false} tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }} />
                                    <Tooltip
                                        cursor={{ fill: 'var(--accent)' }}
                                        contentStyle={{ background: 'var(--popover)', border: '1px solid var(--border)', borderRadius: 8, fontSize: 12 }}
                                        formatter={(v) => [v, 'Check-ins']}
                                    />
                                    <Bar dataKey="count" radius={[6, 6, 2, 2]} fill="var(--gold-2)" />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </Card>

                    <Card className="gap-0 py-5">
                        <div className="flex items-center justify-between px-5 pb-3">
                            <h3 className="font-semibold tracking-tight">Activity</h3>
                            <LiveDot />
                        </div>
                        {activity.length === 0 ? (
                            <p className="text-muted-foreground px-5 py-8 text-center text-sm">Registrations and arrivals will appear here as they happen.</p>
                        ) : (
                            <ol className="relative px-5">
                                {activity.map((a, i) => (
                                    <li key={a.key} className="relative flex gap-3 pb-4 last:pb-0">
                                        {i < activity.length - 1 && <span className="bg-border absolute top-9 bottom-0 left-[17px] w-px" />}
                                        <Link href={`/workspace/people?partner=${a.partner.id}`} className="shrink-0">
                                            <PartnerAvatar name={a.partner.full_name} tier={a.partner.tier} />
                                        </Link>
                                        <div className="min-w-0 flex-1 pt-0.5">
                                            <p className="text-sm leading-snug">
                                                <Link href={`/workspace/people?partner=${a.partner.id}`} className="font-medium hover:underline">
                                                    {a.partner.title} {a.partner.full_name}
                                                </Link>{' '}
                                                <span className="text-muted-foreground">{a.type === 'checked_in' ? 'arrived at the lounge' : 'completed registration'}</span>
                                            </p>
                                            <p className="text-muted-foreground mt-0.5 flex items-center gap-1 text-xs">
                                                {a.type === 'checked_in' ? <ScanLineIcon className="size-3" /> : <CheckCircle2Icon className="size-3" />}
                                                {ago(a.at)}
                                            </p>
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </Card>
                </div>
            </div>
        </WorkspaceLayout>
    );
}
