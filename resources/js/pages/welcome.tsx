import { useEffect, useState, type ReactNode } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowRightIcon,
    BadgeCheckIcon,
    CommandIcon,
    FileSpreadsheetIcon,
    LayersIcon,
    MailCheckIcon,
    MoonIcon,
    RadioIcon,
    ScanLineIcon,
    SendIcon,
    SmartphoneIcon,
    SparklesIcon,
    UsersRoundIcon,
    WifiOffIcon,
} from 'lucide-react';
import { BrandWordmark } from '@/components/brand';
import { LiveDot } from '@/components/workspace/live-dot';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const SHOT = (name: string) => `/images/showcase/${name}.webp`;

const TOUR = [
    {
        key: 'people',
        label: 'People',
        icon: UsersRoundIcon,
        title: 'Every partner on one board',
        text: 'See the whole guest list move from invited to checked in. Group by journey or by lounge, filter in a click, and act on dozens of partners at once.',
        image: 'people',
    },
    {
        key: 'profile',
        label: 'Partner profile',
        icon: BadgeCheckIcon,
        title: 'Everything about a partner, one click away',
        text: 'Contact details, KingsChat, IPPC plans, spouse, QR pass and their full journey. Resend, regenerate a link or edit without leaving the board.',
        image: 'profile',
    },
    {
        key: 'checkins',
        label: 'Check-ins',
        icon: ScanLineIcon,
        title: 'Arrivals, live from the lounge door',
        text: 'Ushers scan passes, the workspace updates by itself. Know who has arrived, who is still expected, and download the report for any date range.',
        image: 'checkins',
    },
    {
        key: 'outreach',
        label: 'Outreach',
        icon: SendIcon,
        title: 'Speak to a whole lounge at once',
        text: 'Write once to Gold, Diamond or any mix of tiers. Delivery health shows what landed, and bounced emails can be retried in one click.',
        image: 'outreach',
    },
    {
        key: 'palette',
        label: 'Search',
        icon: CommandIcon,
        title: 'Find anyone in a heartbeat',
        text: 'Press ⌘K from anywhere to search by name, email, phone, church or KingsChat handle, or jump straight to any part of the workspace.',
        image: 'palette',
    },
];

export default function Welcome() {
    const [tab, setTab] = useState(0);
    const [paused, setPaused] = useState(false);

    // Gently auto-advance the tour until someone interacts with it.
    useEffect(() => {
        if (paused) return;
        const t = window.setInterval(() => setTab((i) => (i + 1) % TOUR.length), 6000);
        return () => window.clearInterval(t);
    }, [paused]);

    return (
        <div className="bg-background min-h-svh overflow-x-clip">
            <Head title="Every partner, welcomed by name" />

            {/* Nav */}
            <header className="bg-background/75 sticky top-0 z-40 border-b border-border/60 backdrop-blur-xl">
                <div className="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4 sm:px-6">
                    <a href="/" aria-label="Angel Lounges home">
                        <BrandWordmark />
                    </a>
                    <nav className="text-muted-foreground ml-auto hidden items-center gap-6 text-sm md:flex">
                        <a href="#journey" className="hover:text-foreground transition-colors">
                            How it works
                        </a>
                        <a href="#tour" className="hover:text-foreground transition-colors">
                            The workspace
                        </a>
                        <a href="#partners" className="hover:text-foreground transition-colors">
                            For partners
                        </a>
                    </nav>
                    <Button asChild variant="outline" size="sm" className="ml-auto md:ml-0">
                        <Link href="/login">Sign in</Link>
                    </Button>
                </div>
            </header>

            {/* Hero */}
            <section className="relative">
                <div className="bg-glow pointer-events-none absolute inset-0" />
                <div className="relative mx-auto max-w-6xl px-4 pt-16 text-center sm:px-6 sm:pt-24">
                    <span className="animate-rise bg-card/80 text-muted-foreground inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium shadow-xs backdrop-blur">
                        <SparklesIcon className="text-primary size-3.5" /> A first look at what we're building for IPPC 2025
                    </span>
                    <h1 className="animate-rise mx-auto mt-6 max-w-5xl font-serif text-balance text-[3.25rem] leading-[0.98] tracking-tight [animation-delay:60ms] sm:text-7xl md:text-[5.5rem]">
                        Every partner, <span className="text-gold italic">welcomed by name.</span>
                    </h1>
                    <p className="animate-rise text-muted-foreground mx-auto mt-6 max-w-2xl text-lg text-balance [animation-delay:120ms]">
                        Invitations, registration and lounge check-in for the Angel Lounge — one calm workspace for the team, and a beautiful personal pass for every partner.
                    </p>
                    <div className="animate-rise mt-9 flex flex-col justify-center gap-3 [animation-delay:180ms] sm:flex-row">
                        <Button asChild variant="gold" size="lg">
                            <a href="#tour">
                                Take the tour <ArrowRightIcon />
                            </a>
                        </Button>
                        <Button asChild variant="outline" size="lg" className="bg-card/70">
                            <Link href="/login">Open the workspace</Link>
                        </Button>
                    </div>
                </div>

                {/* Hero visual */}
                <div className="animate-rise relative mx-auto mt-16 max-w-6xl px-4 [animation-delay:260ms] sm:mt-20 sm:px-6">
                    <div className="pointer-events-none absolute inset-x-[10%] top-[8%] bottom-0 rounded-[3rem] bg-[radial-gradient(closest-side,color-mix(in_oklch,var(--gold-2)_45%,transparent),transparent)] opacity-60 blur-3xl" />
                    <BrowserFrame src={SHOT('home')} alt="The workspace home: the guest journey, lounges and live activity" priority />
                    <PhoneFrame
                        src={SHOT('m-pass')}
                        alt="A partner's lounge pass with their tier-coloured QR code"
                        className="absolute -right-2 -bottom-10 hidden w-[22%] max-w-[230px] rotate-[4deg] md:block lg:-right-8"
                    />
                </div>
            </section>

            {/* Credits */}
            <section className="mx-auto mt-24 max-w-6xl px-4 sm:px-6">
                <div className="text-muted-foreground flex flex-col items-center justify-center gap-5 text-xs tracking-[0.2em] uppercase sm:flex-row sm:gap-10">
                    <span>Made for</span>
                    <div className="flex items-center gap-8 opacity-90">
                        <img src="/images/logo.svg" alt="Angel Lounges" className="h-10 w-auto" loading="lazy" />
                        <img src="/images/ror.jpg" alt="Rhapsody of Realities" className="h-9 w-auto rounded" loading="lazy" />
                        <img src="/images/ippc.jpeg" alt="IPPC" className="h-9 w-auto rounded" loading="lazy" />
                    </div>
                </div>
            </section>

            {/* Journey */}
            <section id="journey" className="mx-auto max-w-6xl scroll-mt-20 px-4 pt-28 sm:px-6">
                <SectionHead eyebrow="How it works" title={<>From the guest list to the lounge door</>}>
                    Every partner moves through four simple steps. The workspace shows exactly where each one is, so nobody slips through the cracks.
                </SectionHead>
                <ol className="relative mt-14 grid gap-4 md:grid-cols-4">
                    <div className="pointer-events-none absolute top-7 right-[12%] left-[12%] hidden h-px bg-gradient-to-r from-transparent via-[var(--gold-2)] to-transparent opacity-50 md:block" />
                    {[
                        { icon: FileSpreadsheetIcon, title: 'Invite', text: 'Import the partner spreadsheet or add someone by hand. Each partner gets a personal registration link by email.' },
                        { icon: MailCheckIcon, title: 'Deliver', text: 'See which invitations landed and which bounced, and retry in a click.' },
                        { icon: SmartphoneIcon, title: 'Register', text: 'Partners confirm their details on their phone and instantly receive a pass coloured by their tier.' },
                        { icon: ScanLineIcon, title: 'Welcome', text: 'Ushers scan the pass at the lounge — even offline — and every arrival appears live.' },
                    ].map((s, i) => (
                        <li key={s.title} className="relative flex flex-col items-center text-center">
                            <span className="bg-gold relative grid size-14 place-items-center rounded-2xl text-[oklch(0.22_0.04_65)] shadow-lg shadow-[var(--gold-2)]/25">
                                <s.icon className="size-6" />
                                <span className="bg-card text-foreground absolute -top-2 -right-2 grid size-6 place-items-center rounded-full border text-[11px] font-semibold shadow-xs">{i + 1}</span>
                            </span>
                            <h3 className="mt-5 font-serif text-2xl">{s.title}</h3>
                            <p className="text-muted-foreground mt-2 max-w-60 text-sm">{s.text}</p>
                        </li>
                    ))}
                </ol>
            </section>

            {/* Tour */}
            <section id="tour" className="mx-auto max-w-6xl scroll-mt-20 px-4 pt-32 sm:px-6">
                <SectionHead eyebrow="The workspace" title={<>Not a spreadsheet. <span className="text-gold italic">A workspace.</span></>}>
                    Built around people and their journey to the lounge, not rows in a table.
                </SectionHead>

                <div className="mt-12 flex justify-center">
                    <div className="bg-muted scrollbar-thin flex max-w-full gap-1 overflow-x-auto rounded-full p-1" role="tablist" aria-label="Workspace tour">
                        {TOUR.map((t, i) => (
                            <button
                                key={t.key}
                                role="tab"
                                aria-selected={tab === i}
                                onClick={() => {
                                    setTab(i);
                                    setPaused(true);
                                }}
                                className={cn(
                                    'relative flex h-9 shrink-0 items-center gap-2 overflow-hidden rounded-full px-4 text-sm font-medium transition-all [&_svg]:size-4',
                                    tab === i ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                <t.icon />
                                {t.label}
                                {tab === i && !paused && (
                                    <span key={tab} className="bg-gold absolute bottom-0 left-0 h-0.5 animate-[grow_6s_linear_forwards]" />
                                )}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="mt-10 grid items-center gap-10 lg:grid-cols-[1fr_2.1fr]">
                    <div key={TOUR[tab].key} className="animate-rise order-2 lg:order-1">
                        <h3 className="font-serif text-4xl leading-tight tracking-tight">{TOUR[tab].title}</h3>
                        <p className="text-muted-foreground mt-4">{TOUR[tab].text}</p>
                        <div className="mt-6 flex gap-1.5">
                            {TOUR.map((t, i) => (
                                <button
                                    key={t.key}
                                    onClick={() => {
                                        setTab(i);
                                        setPaused(true);
                                    }}
                                    aria-label={`Show ${t.label}`}
                                    className={cn('h-1.5 rounded-full transition-all', tab === i ? 'bg-primary w-6' : 'bg-border w-1.5 hover:bg-muted-foreground/40')}
                                />
                            ))}
                        </div>
                    </div>
                    <div className="order-1 lg:order-2">
                        <div className="relative">
                            {TOUR.map((t, i) => (
                                <div
                                    key={t.key}
                                    className={cn('transition-all duration-700', i === tab ? 'relative opacity-100' : 'pointer-events-none absolute inset-0 scale-[0.98] opacity-0')}
                                    aria-hidden={i !== tab}
                                >
                                    <BrowserFrame src={SHOT(t.image)} alt={t.title} />
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            {/* Partners on their phones */}
            <section id="partners" className="relative mt-32 scroll-mt-20 overflow-hidden border-y bg-[oklch(0.18_0.012_60)] text-[oklch(0.95_0.01_85)]">
                <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(50rem_30rem_at_80%_20%,color-mix(in_oklch,var(--gold-2)_28%,transparent),transparent_70%)]" />
                <div className="relative mx-auto grid max-w-6xl items-center gap-14 px-4 py-24 sm:px-6 lg:grid-cols-2">
                    <div>
                        <p className="text-xs font-medium tracking-[0.25em] text-[var(--gold-1)] uppercase">For our partners</p>
                        <h2 className="mt-4 font-serif text-5xl leading-[1.02] tracking-tight sm:text-6xl">
                            Two minutes on their phone. <span className="text-gold italic">A pass to keep.</span>
                        </h2>
                        <p className="mt-6 max-w-lg opacity-75">
                            Partners open their personal link, confirm their details, tell us about IPPC and their spouse, and receive a lounge pass coloured by their tier — ready to show at the door.
                        </p>
                        <ul className="mt-8 grid gap-3 text-sm sm:grid-cols-2">
                            {['Personal link — no account, no password', 'Searchable country codes', 'Gift delivery asked only when needed', 'Ruby, Silver, Gold, Diamond and more'].map((t) => (
                                <li key={t} className="flex items-center gap-2.5 opacity-85">
                                    <BadgeCheckIcon className="size-4 shrink-0 text-[var(--gold-1)]" /> {t}
                                </li>
                            ))}
                        </ul>
                    </div>
                    <div className="relative flex items-end justify-center gap-4 sm:gap-6">
                        <PhoneFrame src={SHOT('m-register')} alt="Registration form on a phone" className="w-[31%] max-w-[210px] -rotate-[5deg] translate-y-6" dark />
                        <PhoneFrame src={SHOT('m-ippc')} alt="IPPC attendance questions" className="z-10 w-[34%] max-w-[230px]" dark />
                        <PhoneFrame src={SHOT('m-pass')} alt="The finished lounge pass" className="w-[31%] max-w-[210px] rotate-[5deg] translate-y-6" dark />
                    </div>
                </div>
            </section>

            {/* Details */}
            <section className="mx-auto max-w-6xl px-4 pt-28 sm:px-6">
                <SectionHead eyebrow="The little things" title="Made for the busy days" />
                <div className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {[
                        { icon: RadioIcon, title: 'Live by default', text: 'Registrations and arrivals appear on their own — nobody hits refresh.', extra: <LiveDot className="ml-auto" /> },
                        { icon: LayersIcon, title: 'Bulk actions', text: 'Select a whole column to resend invitations or generate QR passes at once.' },
                        { icon: CommandIcon, title: 'Keyboard friendly', text: '⌘K to search, G then P for People, F to filter. Fast hands, calm team.' },
                        { icon: WifiOffIcon, title: 'Scanners work offline', text: 'The check-in app downloads the guest list and syncs arrivals when it can.' },
                        { icon: SmartphoneIcon, title: 'Works on a phone', text: 'The whole workspace adapts to small screens for the team on the move.' },
                        { icon: MoonIcon, title: 'Day or night', text: 'A warm light theme and a rich dark theme for late evenings at the lounge.' },
                    ].map((f) => (
                        <div key={f.title} className="bg-card group rounded-2xl border p-6 shadow-xs transition-all hover:-translate-y-0.5 hover:shadow-md">
                            <div className="flex items-center">
                                <span className="bg-accent text-primary grid size-10 place-items-center rounded-xl">
                                    <f.icon className="size-5" />
                                </span>
                                {f.extra}
                            </div>
                            <h3 className="mt-5 font-medium">{f.title}</h3>
                            <p className="text-muted-foreground mt-1.5 text-sm">{f.text}</p>
                        </div>
                    ))}
                </div>

                <div className="mt-6 overflow-hidden rounded-2xl border bg-[oklch(0.16_0.008_60)] p-3 shadow-xl sm:p-4">
                    <div className="grid items-center gap-6 lg:grid-cols-[1fr_2fr]">
                        <div className="p-4 text-[oklch(0.95_0.01_85)] sm:p-6">
                            <MoonIcon className="size-5 text-[var(--gold-1)]" />
                            <h3 className="mt-4 font-serif text-3xl tracking-tight">After dark, too.</h3>
                            <p className="mt-2 text-sm opacity-70">Every screen has a carefully tuned dark theme — easy on the eyes at the evening sessions.</p>
                        </div>
                        <img src={SHOT('people-dark')} alt="The People board in dark mode, grouped by lounge" className="w-full rounded-xl ring-1 ring-white/10" loading="lazy" />
                    </div>
                </div>
            </section>

            {/* CTA */}
            <section className="mx-auto max-w-6xl px-4 py-28 sm:px-6">
                <div className="bg-gold relative overflow-hidden rounded-[2rem] px-6 py-16 text-center text-[oklch(0.2_0.04_65)] shadow-2xl shadow-[var(--gold-2)]/30 sm:px-12">
                    <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(40rem_20rem_at_50%_-20%,rgba(255,255,255,0.45),transparent_70%)]" />
                    <h2 className="relative font-serif text-5xl leading-tight tracking-tight sm:text-6xl">Ready when the doors open.</h2>
                    <p className="relative mx-auto mt-4 max-w-xl opacity-80">This is an early look at what we're cooking for the Angel Lounge at IPPC 2025. We'd love your thoughts.</p>
                    <div className="relative mt-8 flex justify-center">
                        <Button asChild size="lg" className="bg-[oklch(0.2_0.02_60)] text-[oklch(0.96_0.01_85)] hover:bg-[oklch(0.26_0.02_60)]">
                            <Link href="/login">
                                Open the workspace <ArrowRightIcon />
                            </Link>
                        </Button>
                    </div>
                </div>
            </section>

            <footer className="border-t">
                <div className="text-muted-foreground mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-4 py-8 text-xs sm:flex-row sm:px-6">
                    <BrandWordmark />
                    <p>© {new Date().getFullYear()} Angel Lounges · Angel Court · IPPC 2025</p>
                </div>
            </footer>
        </div>
    );
}

function SectionHead({ eyebrow, title, children }: { eyebrow: string; title: ReactNode; children?: ReactNode }) {
    return (
        <div className="mx-auto max-w-2xl text-center">
            <p className="text-primary text-xs font-semibold tracking-[0.25em] uppercase">{eyebrow}</p>
            <h2 className="mt-4 font-serif text-5xl leading-[1.04] tracking-tight text-balance sm:text-6xl">{title}</h2>
            {children && <p className="text-muted-foreground mt-5 text-balance">{children}</p>}
        </div>
    );
}

function BrowserFrame({ src, alt, priority }: { src: string; alt: string; priority?: boolean }) {
    return (
        <div className="bg-card relative overflow-hidden rounded-2xl border shadow-2xl shadow-black/10 ring-1 ring-black/[0.03] dark:shadow-black/40">
            <div className="flex h-9 items-center gap-1.5 border-b bg-[color-mix(in_oklch,var(--muted)_70%,var(--card))] px-4">
                <span className="size-2.5 rounded-full bg-[#ff5f57]" />
                <span className="size-2.5 rounded-full bg-[#febc2e]" />
                <span className="size-2.5 rounded-full bg-[#28c840]" />
                <span className="text-muted-foreground bg-background/70 mx-auto hidden rounded-md px-16 py-0.5 text-[11px] sm:block">angel-lounges / workspace</span>
            </div>
            <img src={src} alt={alt} width={2400} height={1500} className="block w-full" loading={priority ? 'eager' : 'lazy'} fetchPriority={priority ? 'high' : 'auto'} />
        </div>
    );
}

function PhoneFrame({ src, alt, className, dark }: { src: string; alt: string; className?: string; dark?: boolean }) {
    return (
        <div
            className={cn(
                'relative rounded-[2.2rem] border-[6px] p-0 shadow-2xl',
                dark ? 'border-[oklch(0.3_0.01_60)] bg-black shadow-black/50' : 'border-[oklch(0.22_0.01_60)] bg-black shadow-black/25',
                className,
            )}
        >
            <span className="absolute top-2 left-1/2 z-10 h-[5%] w-[32%] max-w-20 -translate-x-1/2 rounded-full bg-black" />
            <img src={src} alt={alt} width={780} height={1328} className="block w-full rounded-[1.75rem]" loading="lazy" />
        </div>
    );
}
