import { useEffect, useMemo, useState, type FormEvent, type ReactNode } from 'react';
import { useForm } from '@inertiajs/react';
import { AtSignIcon, CheckIcon, ChurchIcon, GiftIcon, HeartHandshakeIcon, Loader2Icon, PlaneIcon, SofaIcon, UserRoundIcon } from 'lucide-react';
import PublicLayout from '@/layouts/public-layout';
import { Field } from '@/components/field';
import { ChoiceCard } from '@/components/choice-card';
import { PhoneInput, joinPhone, splitPhone } from '@/components/phone-input';
import { TierBadge } from '@/components/tier';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';

interface Props {
    token: string;
    partner: {
        title: string | null;
        designation: string | null;
        full_name: string;
        email: string | null;
        phone: string | null;
        kc_handle: string | null;
        region: string | null;
        zone: string | null;
        group: string | null;
        church: string | null;
        tier: string;
        ror_copies_sponsored: number | null;
        will_attend_ippc: boolean;
        will_be_at_exhibition: boolean;
        delivery_method: string | null;
        coming_with_spouse: boolean;
        spouse_title: string | null;
        spouse_name: string | null;
        spouse_kc_handle: string | null;
    };
    options: { titles: string[]; designations: string[] };
}

const SECTIONS = [
    { id: 'you', label: 'About you', icon: UserRoundIcon },
    { id: 'church', label: 'Your church', icon: ChurchIcon },
    { id: 'ippc', label: 'IPPC 2025', icon: PlaneIcon },
    { id: 'spouse', label: 'Your spouse', icon: HeartHandshakeIcon },
] as const;

const FIELD_SECTION: Record<string, (typeof SECTIONS)[number]['id']> = {
    title: 'you',
    designation: 'you',
    full_name: 'you',
    phone: 'you',
    kc_handle: 'you',
    region: 'church',
    zone: 'church',
    group: 'church',
    church: 'church',
    will_attend_ippc: 'ippc',
    will_be_at_exhibition: 'ippc',
    delivery_method: 'ippc',
    coming_with_spouse: 'spouse',
    spouse_title: 'spouse',
    spouse_name: 'spouse',
    spouse_kc_handle: 'spouse',
};

export default function Register({ token, partner, options }: Props) {
    const initialPhone = splitPhone(partner.phone);
    const [phoneCode, setPhoneCode] = useState(initialPhone.code);

    const form = useForm({
        title: partner.title ?? '',
        designation: partner.designation ?? '',
        full_name: partner.full_name === 'Guest' ? '' : (partner.full_name ?? ''),
        phone: initialPhone.number,
        kc_handle: partner.kc_handle ?? '',
        region: partner.region ?? '',
        zone: partner.zone ?? '',
        group: partner.group ?? '',
        church: partner.church ?? '',
        ror_copies_sponsored: partner.ror_copies_sponsored ?? 0,
        will_attend_ippc: partner.will_attend_ippc,
        will_be_at_exhibition: partner.will_be_at_exhibition,
        delivery_method: partner.delivery_method ?? '',
        coming_with_spouse: partner.coming_with_spouse,
        spouse_title: partner.spouse_title ?? '',
        spouse_name: partner.spouse_name ?? '',
        spouse_kc_handle: partner.spouse_kc_handle ?? '',
    });
    const { data, setData, errors, processing } = form;

    const needsDelivery = !(data.will_attend_ippc && data.will_be_at_exhibition);

    const done = useMemo(
        () => ({
            you: Boolean(data.title && data.designation && data.full_name && data.kc_handle),
            church: Boolean(data.region || data.zone || data.group || data.church),
            ippc: !needsDelivery || data.delivery_method.trim().length > 0,
            spouse: !data.coming_with_spouse || Boolean(data.spouse_title && data.spouse_name),
        }),
        [data, needsDelivery],
    );
    const progress = Object.values(done).filter(Boolean).length;

    // Jump to the first section that has a server-side error.
    useEffect(() => {
        const first = Object.keys(errors)[0];
        if (first) document.getElementById(`section-${FIELD_SECTION[first] ?? 'you'}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, [errors]);

    function submit(e: FormEvent) {
        e.preventDefault();
        form.transform((d) => ({
            ...d,
            phone: joinPhone(phoneCode, d.phone),
            will_be_at_exhibition: d.will_attend_ippc ? d.will_be_at_exhibition : false,
            delivery_method: needsDelivery ? d.delivery_method : '',
        }));
        form.post(`/partner/register/${token}`, { preserveScroll: true });
    }

    return (
        <PublicLayout title="Complete your registration">
            <section className="animate-rise mx-auto max-w-3xl pt-6 pb-10 text-center sm:pt-12">
                <p className="text-muted-foreground mb-4 text-xs font-medium tracking-[0.25em] uppercase">Partner registration</p>
                <h1 className="font-serif text-5xl leading-[1.05] tracking-tight sm:text-6xl">
                    Welcome to the <span className="text-gold italic">Angel Lounge</span>
                </h1>
                <p className="text-muted-foreground mx-auto mt-5 max-w-xl text-balance">
                    A few details and your personal QR pass will be on its way. It takes about two minutes.
                </p>
                <div className="mt-6 flex flex-wrap items-center justify-center gap-2 text-sm">
                    <span className="text-muted-foreground">Invited as</span>
                    <TierBadge tier={partner.tier} className="bg-card px-3 py-1 text-sm" />
                    {partner.email && <span className="text-muted-foreground">· {partner.email}</span>}
                </div>
            </section>

            <form onSubmit={submit} className="grid gap-8 lg:grid-cols-[220px_1fr]">
                {/* Progress rail */}
                <aside className="hidden lg:block">
                    <nav className="sticky top-6 space-y-1">
                        {SECTIONS.map((s, i) => {
                            const complete = done[s.id];
                            const hasError = Object.keys(errors).some((k) => FIELD_SECTION[k] === s.id);
                            return (
                                <a
                                    key={s.id}
                                    href={`#section-${s.id}`}
                                    className="hover:bg-accent/60 flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors"
                                >
                                    <span
                                        className={cn(
                                            'grid size-6 place-items-center rounded-full border text-[11px] font-semibold transition-all',
                                            hasError
                                                ? 'border-destructive text-destructive'
                                                : complete
                                                  ? 'bg-primary border-primary text-primary-foreground'
                                                  : 'text-muted-foreground',
                                        )}
                                    >
                                        {complete && !hasError ? <CheckIcon className="size-3.5" strokeWidth={3} /> : i + 1}
                                    </span>
                                    <span className={cn(complete ? 'text-foreground' : 'text-muted-foreground')}>{s.label}</span>
                                </a>
                            );
                        })}
                        <div className="px-3 pt-4">
                            <div className="bg-muted h-1 overflow-hidden rounded-full">
                                <div className="bg-gold h-full rounded-full transition-all duration-500" style={{ width: `${(progress / SECTIONS.length) * 100}%` }} />
                            </div>
                            <p className="text-muted-foreground mt-2 text-xs">
                                {progress} of {SECTIONS.length} complete
                            </p>
                        </div>
                    </nav>
                </aside>

                <div className="min-w-0 space-y-6">
                    <Section id="you" step={1} title="About you" description="How should we address you at the lounge?">
                        <div className="grid gap-5 sm:grid-cols-2">
                            <Field label="Title" required error={errors.title}>
                                <Select value={data.title} onValueChange={(v) => setData('title', v)}>
                                    <SelectTrigger aria-invalid={!!errors.title || undefined}>
                                        <SelectValue placeholder="Select title" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {options.titles.map((t) => (
                                            <SelectItem key={t} value={t}>
                                                {t}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>
                            <Field label="Designation" required error={errors.designation}>
                                <Select value={data.designation} onValueChange={(v) => setData('designation', v)}>
                                    <SelectTrigger aria-invalid={!!errors.designation || undefined}>
                                        <SelectValue placeholder="Select designation" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {options.designations.map((t) => (
                                            <SelectItem key={t} value={t}>
                                                {t}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>
                            <Field label="Full name" htmlFor="full_name" required error={errors.full_name} className="sm:col-span-2">
                                <Input
                                    id="full_name"
                                    value={data.full_name}
                                    onChange={(e) => setData('full_name', e.target.value)}
                                    placeholder="As you'd like it on your pass"
                                    autoComplete="name"
                                    required
                                    aria-invalid={!!errors.full_name || undefined}
                                />
                            </Field>
                            <Field label="Email address" hint="This is where your QR pass will be sent.">
                                <Input value={partner.email ?? ''} disabled readOnly />
                            </Field>
                            <Field label="Phone number" htmlFor="phone" error={errors.phone}>
                                <PhoneInput
                                    id="phone"
                                    code={phoneCode}
                                    number={data.phone}
                                    onCodeChange={setPhoneCode}
                                    onNumberChange={(n) => setData('phone', n)}
                                    invalid={!!errors.phone}
                                />
                            </Field>
                            <Field label="KingsChat handle" htmlFor="kc_handle" required error={errors.kc_handle} className="sm:col-span-2">
                                <div className="relative">
                                    <AtSignIcon className="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                                    <Input
                                        id="kc_handle"
                                        value={data.kc_handle}
                                        onChange={(e) => setData('kc_handle', e.target.value)}
                                        placeholder="username"
                                        className="pl-9"
                                        required
                                    />
                                </div>
                            </Field>
                        </div>
                    </Section>

                    <Section id="church" step={2} title="Your church" description="Helps us seat you with your zone.">
                        <div className="grid gap-5 sm:grid-cols-2">
                            {(
                                [
                                    ['region', 'Region', 'e.g. Region 1'],
                                    ['zone', 'Zone', 'e.g. Lagos Zone 1'],
                                    ['group', 'Group', 'Your group'],
                                    ['church', 'Church', 'Your church name'],
                                ] as const
                            ).map(([key, label, placeholder]) => (
                                <Field key={key} label={label} htmlFor={key} error={errors[key]}>
                                    <Input id={key} value={data[key]} onChange={(e) => setData(key, e.target.value)} placeholder={placeholder} />
                                </Field>
                            ))}
                        </div>
                    </Section>

                    <Section id="ippc" step={3} title="IPPC 2025" description="Let us know your plans so we can prepare for you.">
                        <div className="space-y-3">
                            <div className="bg-muted/50 flex items-center justify-between gap-4 rounded-xl border border-dashed p-4">
                                <div className="flex items-center gap-3">
                                    <span className="bg-card grid size-10 place-items-center rounded-lg border">
                                        <GiftIcon className="text-primary size-5" />
                                    </span>
                                    <div>
                                        <p className="text-sm font-medium">ROR copies sponsored</p>
                                        <p className="text-muted-foreground text-xs">Recorded by the partnership team</p>
                                    </div>
                                </div>
                                <span className="font-serif text-3xl tabular-nums">{Number(data.ror_copies_sponsored || 0).toLocaleString()}</span>
                            </div>
                            <ChoiceCard
                                checked={data.will_attend_ippc}
                                onChange={(v) => {
                                    setData((d) => ({ ...d, will_attend_ippc: v, will_be_at_exhibition: v ? d.will_be_at_exhibition : false }));
                                }}
                                icon={<PlaneIcon className="size-5" />}
                                title="I will attend IPPC 2025"
                                description="The International Pastors and Partners Conference"
                            />
                            <div className={cn('grid transition-all duration-300', data.will_attend_ippc ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0')}>
                                <div className="overflow-hidden">
                                    <ChoiceCard
                                        checked={data.will_be_at_exhibition}
                                        onChange={(v) => setData('will_be_at_exhibition', v)}
                                        icon={<SofaIcon className="size-5" />}
                                        title="I'll be your honoured guest at the Angel Lounge"
                                        description="Join us at the Angel Court exhibition lounge"
                                    />
                                </div>
                            </div>
                            <div className={cn('grid transition-all duration-300', needsDelivery ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0')}>
                                <div className="overflow-hidden p-px">
                                    <Field
                                        label="How should we deliver your ROR gifts?"
                                        htmlFor="delivery_method"
                                        required
                                        error={errors.delivery_method}
                                        hint="Needed if you're not attending both IPPC and the Angel Lounge."
                                        className="pt-2"
                                    >
                                        <Textarea
                                            id="delivery_method"
                                            rows={3}
                                            value={data.delivery_method}
                                            onChange={(e) => setData('delivery_method', e.target.value)}
                                            placeholder="Name and contact details of your liaison person"
                                            required={needsDelivery}
                                            aria-invalid={!!errors.delivery_method || undefined}
                                        />
                                    </Field>
                                </div>
                            </div>
                        </div>
                    </Section>

                    <Section id="spouse" step={4} title="Your spouse" description="We'd love to welcome you both.">
                        <ChoiceCard
                            checked={data.coming_with_spouse}
                            onChange={(v) => setData('coming_with_spouse', v)}
                            icon={<HeartHandshakeIcon className="size-5" />}
                            title="I'm coming with my spouse"
                            description="Your spouse will be included on your pass"
                        />
                        <div className={cn('grid transition-all duration-300', data.coming_with_spouse ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0')}>
                            <div className="overflow-hidden p-px">
                                <div className="grid gap-5 pt-5 sm:grid-cols-2">
                                    <Field label="Spouse title" required error={errors.spouse_title}>
                                        <Select value={data.spouse_title} onValueChange={(v) => setData('spouse_title', v)}>
                                            <SelectTrigger aria-invalid={!!errors.spouse_title || undefined}>
                                                <SelectValue placeholder="Select title" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {options.titles.map((t) => (
                                                    <SelectItem key={t} value={t}>
                                                        {t}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </Field>
                                    <Field label="Spouse name" htmlFor="spouse_name" required error={errors.spouse_name}>
                                        <Input
                                            id="spouse_name"
                                            value={data.spouse_name}
                                            onChange={(e) => setData('spouse_name', e.target.value)}
                                            placeholder="Full name"
                                            required={data.coming_with_spouse}
                                            aria-invalid={!!errors.spouse_name || undefined}
                                        />
                                    </Field>
                                    <Field label="Spouse KingsChat handle" htmlFor="spouse_kc" error={errors.spouse_kc_handle} className="sm:col-span-2">
                                        <div className="relative">
                                            <AtSignIcon className="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                                            <Input
                                                id="spouse_kc"
                                                value={data.spouse_kc_handle}
                                                onChange={(e) => setData('spouse_kc_handle', e.target.value)}
                                                placeholder="username"
                                                className="pl-9"
                                            />
                                        </div>
                                    </Field>
                                </div>
                            </div>
                        </div>
                    </Section>

                    <div className="bg-card flex flex-col items-center gap-4 rounded-2xl border p-6 text-center shadow-xs sm:flex-row sm:text-left">
                        <p className="text-muted-foreground flex-1 text-sm">By submitting, you confirm that all the information you've provided is accurate.</p>
                        <Button type="submit" variant="gold" size="lg" disabled={processing} className="w-full sm:w-auto">
                            {processing && <Loader2Icon className="animate-spin" />}
                            Complete registration
                        </Button>
                    </div>
                </div>
            </form>
        </PublicLayout>
    );
}

function Section({ id, step, title, description, children }: { id: string; step: number; title: string; description: string; children: ReactNode }) {
    return (
        <section id={`section-${id}`} className="bg-card scroll-mt-6 rounded-2xl border p-6 shadow-xs sm:p-8">
            <header className="mb-6 flex items-start gap-4">
                <span className="text-muted-foreground font-serif text-2xl leading-none tabular-nums italic">{String(step).padStart(2, '0')}</span>
                <div>
                    <h2 className="text-lg font-semibold tracking-tight">{title}</h2>
                    <p className="text-muted-foreground text-sm">{description}</p>
                </div>
            </header>
            {children}
        </section>
    );
}
