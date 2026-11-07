import type { FormEvent } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import { AlertTriangleIcon, CheckCircle2Icon, ClockIcon, Loader2Icon, MailIcon, RotateCwIcon, SendIcon } from 'lucide-react';
import WorkspaceLayout from '@/layouts/workspace-layout';
import { Field } from '@/components/field';
import { PartnerAvatar } from '@/components/partner-avatar';
import { TierDot } from '@/components/tier';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { TIERS, TIER_META } from '@/lib/tiers';
import { ago, pct } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { Tier } from '@/types';

interface Props {
    audience: Record<Tier, number>;
    health: { sent: number; pending: number; failed: number; none: number };
    failures: { id: number; title: string | null; full_name: string; email: string | null; tier: string; email_response: string | null; updated_at: string | null }[];
    defaults: { subject: string; message: string };
}

export default function Outreach({ audience, health, failures, defaults }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        tiers: ['silver', 'gold', 'diamond'] as string[],
        registered_only: false,
        subject: defaults.subject,
        message: defaults.message,
    });

    const recipients = data.tiers.reduce((sum, t) => sum + (audience[t as Tier] ?? 0), 0);
    const total = health.sent + health.pending + health.failed + health.none;

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/workspace/outreach', { preserveScroll: true });
    }

    function toggleTier(t: string) {
        setData('tiers', data.tiers.includes(t) ? data.tiers.filter((x) => x !== t) : [...data.tiers, t]);
    }

    return (
        <WorkspaceLayout title="Outreach">
            <div className="grid gap-6 xl:grid-cols-[1.35fr_1fr]">
                <Card>
                    <CardHeader>
                        <CardTitle className="font-serif text-3xl font-normal tracking-tight">Write to your partners</CardTitle>
                        <CardDescription>Send an announcement to whole lounges at once. Each email is queued and retried automatically.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <Field label="Who should receive it?" error={errors.tiers}>
                                <div className="flex flex-wrap gap-2">
                                    {TIERS.map((t) => {
                                        const on = data.tiers.includes(t);
                                        return (
                                            <button
                                                key={t}
                                                type="button"
                                                onClick={() => toggleTier(t)}
                                                className={cn(
                                                    'flex h-9 items-center gap-2 rounded-full border px-3.5 text-sm transition-all',
                                                    on ? 'border-primary/60 bg-primary/[0.08] shadow-xs' : 'text-muted-foreground hover:bg-accent/50',
                                                )}
                                            >
                                                <TierDot tier={t} />
                                                {TIER_META[t].short}
                                                <span className="text-muted-foreground tabular-nums">{audience[t] ?? 0}</span>
                                            </button>
                                        );
                                    })}
                                </div>
                            </Field>
                            <Label className="font-normal">
                                <Switch checked={data.registered_only} onCheckedChange={(v) => setData('registered_only', v)} />
                                Only partners who have completed registration
                            </Label>
                            <Field label="Subject" htmlFor="subject" required error={errors.subject}>
                                <Input id="subject" value={data.subject} onChange={(e) => setData('subject', e.target.value)} />
                            </Field>
                            <Field label="Message" htmlFor="message" required error={errors.message} hint="Sent as plain text, exactly as written.">
                                <Textarea id="message" rows={11} value={data.message} onChange={(e) => setData('message', e.target.value)} className="leading-relaxed" />
                            </Field>
                            <div className="flex flex-col-reverse items-center gap-3 border-t pt-5 sm:flex-row">
                                <p className="text-muted-foreground flex-1 text-sm">
                                    {data.registered_only ? 'Up to ' : ''}
                                    <span className="text-foreground font-medium tabular-nums">{recipients.toLocaleString()}</span> recipient{recipients === 1 ? '' : 's'}
                                </p>
                                <Button type="submit" variant="gold" disabled={processing || data.tiers.length === 0} className="w-full sm:w-auto">
                                    {processing ? <Loader2Icon className="animate-spin" /> : <SendIcon />} Queue emails
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <div className="space-y-6">
                    <Card className="gap-4">
                        <CardHeader>
                            <CardTitle className="text-base">Delivery health</CardTitle>
                            <CardDescription>Invitation and announcement emails across the guest list.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="bg-muted flex h-2.5 overflow-hidden rounded-full">
                                <div className="bg-success h-full" style={{ width: `${pct(health.sent, total)}%` }} />
                                <div className="bg-warning h-full" style={{ width: `${pct(health.pending, total)}%` }} />
                                <div className="bg-destructive h-full" style={{ width: `${pct(health.failed, total)}%` }} />
                            </div>
                            <dl className="mt-5 grid grid-cols-2 gap-4">
                                {[
                                    { label: 'Delivered', value: health.sent, icon: CheckCircle2Icon, cls: 'text-success' },
                                    { label: 'In the queue', value: health.pending, icon: ClockIcon, cls: 'text-warning' },
                                    { label: 'Failed', value: health.failed, icon: AlertTriangleIcon, cls: 'text-destructive' },
                                    { label: 'Never emailed', value: health.none, icon: MailIcon, cls: 'text-muted-foreground' },
                                ].map((s) => (
                                    <div key={s.label} className="flex items-center gap-3">
                                        <s.icon className={cn('size-4', s.cls)} />
                                        <div>
                                            <dd className="text-lg font-semibold tabular-nums">{s.value.toLocaleString()}</dd>
                                            <dt className="text-muted-foreground text-xs">{s.label}</dt>
                                        </div>
                                    </div>
                                ))}
                            </dl>
                        </CardContent>
                    </Card>

                    <Card className="gap-0 py-0">
                        <div className="flex items-center justify-between border-b p-4">
                            <h3 className="font-semibold tracking-tight">Needs a retry</h3>
                            {failures.length > 0 && (
                                <Button
                                    size="sm"
                                    variant="outline"
                                    onClick={() => router.post('/workspace/people/bulk', { action: 'resend', ids: failures.map((f) => f.id) }, { preserveScroll: true })}
                                >
                                    <RotateCwIcon /> Retry all
                                </Button>
                            )}
                        </div>
                        {failures.length === 0 ? (
                            <p className="text-muted-foreground px-4 py-12 text-center text-sm">
                                <CheckCircle2Icon className="text-success mx-auto mb-2 size-5" />
                                Every email went through.
                            </p>
                        ) : (
                            <ul className="scrollbar-thin max-h-[26rem] overflow-y-auto">
                                {failures.map((f) => (
                                    <li key={f.id} className="flex items-start gap-3 border-b px-4 py-3 last:border-0">
                                        <PartnerAvatar name={f.full_name} tier={f.tier} className="size-8 text-[11px]" />
                                        <Link href={`/workspace/people?partner=${f.id}`} className="min-w-0 flex-1">
                                            <p className="truncate text-sm font-medium hover:underline">
                                                {f.title} {f.full_name}
                                            </p>
                                            <p className="text-muted-foreground truncate text-xs">{f.email}</p>
                                            {f.email_response && <p className="text-destructive/90 mt-1 line-clamp-2 text-xs">{f.email_response}</p>}
                                        </Link>
                                        <div className="flex flex-col items-end gap-1">
                                            <Button size="sm" variant="ghost" onClick={() => router.post(`/workspace/people/${f.id}/resend`, {}, { preserveScroll: true })}>
                                                Retry
                                            </Button>
                                            <span className="text-muted-foreground text-[11px]">{ago(f.updated_at)}</span>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Card>
                </div>
            </div>
        </WorkspaceLayout>
    );
}
