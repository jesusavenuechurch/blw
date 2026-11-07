import type { FormEvent, ReactNode } from 'react';
import { useForm } from '@inertiajs/react';
import { HeartHandshakeIcon, Loader2Icon, PlaneIcon, SofaIcon } from 'lucide-react';
import { Field } from '@/components/field';
import { ChoiceCard } from '@/components/choice-card';
import { TierDot } from '@/components/tier';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { TIER_META } from '@/lib/tiers';
import { cn } from '@/lib/utils';
import type { FormOptions, PartnerDetail } from '@/types';

export function PartnerForm({
    partner,
    options,
    onDone,
    onCancel,
}: {
    partner?: PartnerDetail | null;
    options: FormOptions;
    onDone?: () => void;
    onCancel?: () => void;
}) {
    const editing = Boolean(partner);
    const { data, setData, post, put, processing, errors, transform } = useForm({
        title: partner?.title ?? '',
        designation: partner?.designation ?? '',
        full_name: partner?.full_name ?? '',
        email: partner?.email ?? '',
        phone: partner?.phone ?? '',
        region: partner?.region ?? '',
        zone: partner?.zone ?? '',
        group: partner?.group ?? '',
        church: partner?.church ?? '',
        tier: (partner?.tier as string) ?? 'ruby',
        ror_copies_sponsored: partner?.ror_copies_sponsored ?? 0,
        will_attend_ippc: partner?.will_attend_ippc ?? false,
        will_be_at_exhibition: partner?.will_be_at_exhibition ?? false,
        delivery_method: partner?.delivery_method ?? '',
        coming_with_spouse: partner?.coming_with_spouse ?? false,
        spouse_title: partner?.spouse_title ?? '',
        spouse_name: partner?.spouse_name ?? '',
        spouse_kc_handle: partner?.spouse_kc_handle ?? '',
    });

    const needsDelivery = !(data.will_attend_ippc && data.will_be_at_exhibition);

    function submit(e: FormEvent) {
        e.preventDefault();
        transform((d) => ({ ...d, ror_copies_sponsored: Number(d.ror_copies_sponsored) || 0 }));
        const opts = { preserveScroll: true, preserveState: true, onSuccess: () => onDone?.() };
        if (editing && partner) put(`/workspace/people/${partner.id}`, opts);
        else post('/workspace/people', opts);
    }

    const select = (key: 'title' | 'designation' | 'spouse_title', items: string[], placeholder: string) => (
        <Select value={data[key]} onValueChange={(v) => setData(key, v)}>
            <SelectTrigger aria-invalid={!!errors[key] || undefined}>
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
                {items.map((t) => (
                    <SelectItem key={t} value={t}>
                        {t}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );

    return (
        <form onSubmit={submit} className="flex min-h-0 flex-1 flex-col">
            <div className="scrollbar-thin flex-1 space-y-8 overflow-y-auto px-6 pb-6">
                <Group title="Partner">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Title" required error={errors.title}>
                            {select('title', options.titles, 'Select title')}
                        </Field>
                        <Field label="Designation" required error={errors.designation}>
                            {select('designation', options.designations, 'Select designation')}
                        </Field>
                        <Field label="Full name" required error={errors.full_name} className="sm:col-span-2">
                            <Input value={data.full_name} onChange={(e) => setData('full_name', e.target.value)} aria-invalid={!!errors.full_name || undefined} />
                        </Field>
                        <Field label="Email" required error={errors.email}>
                            <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} aria-invalid={!!errors.email || undefined} />
                        </Field>
                        <Field label="Phone" error={errors.phone}>
                            <Input type="tel" value={data.phone} onChange={(e) => setData('phone', e.target.value)} />
                        </Field>
                    </div>
                </Group>

                <Group title="Lounge">
                    <Field label="Partnership tier" required error={errors.tier} hint="The QR code colour follows the tier.">
                        <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            {options.tiers.map((t) => (
                                <button
                                    key={t}
                                    type="button"
                                    onClick={() => setData('tier', t)}
                                    className={cn(
                                        'flex items-center gap-2 rounded-lg border px-3 py-2 text-left text-sm transition-all',
                                        data.tier === t ? 'border-primary/60 bg-primary/[0.07] shadow-xs' : 'hover:bg-accent/50',
                                    )}
                                >
                                    <TierDot tier={t} className="size-2.5" />
                                    <span className="truncate">{TIER_META[t].short}</span>
                                </button>
                            ))}
                        </div>
                    </Field>
                    <Field label="ROR copies sponsored" error={errors.ror_copies_sponsored} className="mt-4 max-w-48">
                        <Input
                            type="number"
                            min={0}
                            value={data.ror_copies_sponsored}
                            onChange={(e) => setData('ror_copies_sponsored', e.target.value as unknown as number)}
                        />
                    </Field>
                </Group>

                <Group title="Church">
                    <div className="grid gap-4 sm:grid-cols-2">
                        {(['region', 'zone', 'group', 'church'] as const).map((k) => (
                            <Field key={k} label={k[0].toUpperCase() + k.slice(1)} error={errors[k]}>
                                <Input value={data[k]} onChange={(e) => setData(k, e.target.value)} />
                            </Field>
                        ))}
                    </div>
                </Group>

                <Group title="IPPC 2025">
                    <div className="space-y-2.5">
                        <ChoiceCard
                            checked={data.will_attend_ippc}
                            onChange={(v) => setData((d) => ({ ...d, will_attend_ippc: v, will_be_at_exhibition: v ? d.will_be_at_exhibition : false }))}
                            icon={<PlaneIcon className="size-5" />}
                            title="Attending IPPC 2025"
                        />
                        {data.will_attend_ippc && (
                            <ChoiceCard
                                checked={data.will_be_at_exhibition}
                                onChange={(v) => setData('will_be_at_exhibition', v)}
                                icon={<SofaIcon className="size-5" />}
                                title="Honoured guest at the Angel Lounge"
                            />
                        )}
                        {needsDelivery && (
                            <Field label="How should the ROR gifts be delivered?" required error={errors.delivery_method} className="pt-2">
                                <Textarea
                                    rows={3}
                                    value={data.delivery_method}
                                    onChange={(e) => setData('delivery_method', e.target.value)}
                                    placeholder="Name and contact information of the liaison person"
                                    aria-invalid={!!errors.delivery_method || undefined}
                                />
                            </Field>
                        )}
                    </div>
                </Group>

                <Group title="Spouse">
                    <ChoiceCard
                        checked={data.coming_with_spouse}
                        onChange={(v) => setData('coming_with_spouse', v)}
                        icon={<HeartHandshakeIcon className="size-5" />}
                        title="Coming with spouse"
                    />
                    {data.coming_with_spouse && (
                        <div className="mt-4 grid gap-4 sm:grid-cols-2">
                            <Field label="Spouse title" required error={errors.spouse_title}>
                                {select('spouse_title', options.titles, 'Select title')}
                            </Field>
                            <Field label="Spouse name" required error={errors.spouse_name}>
                                <Input value={data.spouse_name} onChange={(e) => setData('spouse_name', e.target.value)} aria-invalid={!!errors.spouse_name || undefined} />
                            </Field>
                            <Field label="Spouse KC handle" error={errors.spouse_kc_handle} className="sm:col-span-2">
                                <Input value={data.spouse_kc_handle} onChange={(e) => setData('spouse_kc_handle', e.target.value)} />
                            </Field>
                        </div>
                    )}
                </Group>
            </div>
            <div className="bg-background/90 flex items-center justify-end gap-2 border-t px-6 py-4 backdrop-blur">
                {!editing && <p className="text-muted-foreground mr-auto text-xs">Their invitation email is sent as soon as you save.</p>}
                {onCancel && (
                    <Button type="button" variant="ghost" onClick={onCancel}>
                        Cancel
                    </Button>
                )}
                <Button type="submit" variant="gold" disabled={processing}>
                    {processing && <Loader2Icon className="animate-spin" />}
                    {editing ? 'Save changes' : 'Add & send invitation'}
                </Button>
            </div>
        </form>
    );
}

function Group({ title, children }: { title: string; children: ReactNode }) {
    return (
        <section>
            <h4 className="text-muted-foreground mb-3 text-[11px] font-semibold tracking-wider uppercase">{title}</h4>
            {children}
        </section>
    );
}
