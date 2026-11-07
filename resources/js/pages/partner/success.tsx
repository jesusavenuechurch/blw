import { CalendarCheckIcon, DownloadIcon, MailCheckIcon, QrCodeIcon, SparklesIcon } from 'lucide-react';
import PublicLayout from '@/layouts/public-layout';
import { Button } from '@/components/ui/button';
import { TierBadge } from '@/components/tier';
import { tierMeta } from '@/lib/tiers';
import { when } from '@/lib/format';

interface Props {
    partner: {
        title: string | null;
        full_name: string;
        tier: string;
        ror_copies_sponsored: number;
        qr_code_url: string | null;
        photo_url: string | null;
        registered_at: string | null;
        will_attend_ippc: boolean;
        will_be_at_exhibition: boolean;
        coming_with_spouse: boolean;
        spouse_name: string | null;
    };
}

export default function Success({ partner }: Props) {
    const color = tierMeta(partner.tier).color;

    return (
        <PublicLayout title="You're registered">
            <div className="mx-auto grid max-w-5xl items-center gap-10 pt-6 sm:pt-12 lg:grid-cols-[1fr_380px]">
                <div className="animate-rise">
                    <span className="bg-success/12 text-success inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium">
                        <SparklesIcon className="size-3.5" /> Registration complete
                    </span>
                    <h1 className="mt-5 font-serif text-5xl leading-[1.05] tracking-tight sm:text-6xl">
                        Thank you, <span className="text-gold italic">{partner.title ? `${partner.title} ` : ''}{partner.full_name.split(' ')[0]}</span>.
                    </h1>
                    <p className="text-muted-foreground mt-5 max-w-lg text-balance">
                        Your place at the Angel Lounge is confirmed. Keep your QR pass handy — it's your key to the lounge.
                    </p>

                    <ul className="mt-10 space-y-5">
                        {[
                            { icon: MailCheckIcon, title: 'Check your inbox', text: 'A confirmation email with your QR pass is on its way.' },
                            { icon: QrCodeIcon, title: 'Save your QR pass', text: 'Download it now and present it for lounge access.' },
                            { icon: CalendarCheckIcon, title: 'Watch for updates', text: "We'll send event updates and reminders as IPPC approaches." },
                        ].map(({ icon: Icon, title, text }) => (
                            <li key={title} className="flex gap-4">
                                <span className="bg-card grid size-10 shrink-0 place-items-center rounded-xl border shadow-xs">
                                    <Icon className="text-primary size-5" />
                                </span>
                                <div>
                                    <p className="font-medium">{title}</p>
                                    <p className="text-muted-foreground text-sm">{text}</p>
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>

                {/* The pass */}
                <div className="animate-rise [animation-delay:120ms]">
                    <div
                        className="relative overflow-hidden rounded-[1.75rem] border bg-card p-6 shadow-2xl shadow-black/10"
                        style={{ backgroundImage: `radial-gradient(28rem 14rem at 50% -20%, color-mix(in oklch, ${color} 22%, transparent), transparent 70%)` }}
                    >
                        <div className="flex items-center justify-between">
                            <span className="text-muted-foreground text-[10px] font-semibold tracking-[0.25em] uppercase">Lounge pass</span>
                            <TierBadge tier={partner.tier} className="bg-card" />
                        </div>
                        <div className="mt-6 grid place-items-center">
                            {partner.qr_code_url ? (
                                <div className="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-black/5">
                                    <img src={partner.qr_code_url} alt="Your QR code" className="size-52 object-contain" />
                                </div>
                            ) : (
                                <div className="text-muted-foreground grid size-56 place-items-center rounded-2xl border border-dashed text-sm">QR code coming by email</div>
                            )}
                        </div>
                        <div className="mt-6 flex items-center gap-4 border-t border-dashed pt-5">
                            {partner.photo_url && (
                                <img src={partner.photo_url} alt="" className="size-16 shrink-0 rounded-full object-cover ring-2 ring-[color-mix(in_oklch,var(--gold-2)_50%,transparent)]" />
                            )}
                            <div className="min-w-0">
                            <p className="font-serif text-2xl leading-tight">
                                {partner.title} {partner.full_name}
                            </p>
                            {partner.coming_with_spouse && partner.spouse_name && (
                                <p className="text-muted-foreground mt-1 text-sm">with {partner.spouse_name}</p>
                            )}
                            </div>
                        </div>
                        <div>
                            <dl className="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt className="text-muted-foreground text-xs">ROR copies</dt>
                                    <dd className="font-medium tabular-nums">{partner.ror_copies_sponsored.toLocaleString()}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground text-xs">Registered</dt>
                                    <dd className="font-medium">{when(partner.registered_at)}</dd>
                                </div>
                            </dl>
                        </div>
                        {partner.qr_code_url && (
                            <Button asChild variant="gold" size="lg" className="mt-6 w-full">
                                <a href={partner.qr_code_url} download={`QR_${partner.full_name}.png`}>
                                    <DownloadIcon /> Download QR pass
                                </a>
                            </Button>
                        )}
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
