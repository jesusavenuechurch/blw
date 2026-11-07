import { Link } from '@inertiajs/react';
import { ArrowRightIcon, QrCodeIcon, SofaIcon, UsersRoundIcon } from 'lucide-react';
import PublicLayout from '@/layouts/public-layout';
import { Button } from '@/components/ui/button';

export default function Welcome() {
    return (
        <PublicLayout title="Guest management">
            <section className="animate-rise mx-auto max-w-3xl pt-16 pb-20 text-center sm:pt-24">
                <p className="text-muted-foreground mb-5 text-xs font-medium tracking-[0.25em] uppercase">Angel Court · IPPC 2025</p>
                <h1 className="font-serif text-6xl leading-[1] tracking-tight sm:text-7xl">
                    Hosting the <span className="text-gold italic">best in the world</span>
                </h1>
                <p className="text-muted-foreground mx-auto mt-6 max-w-xl text-lg text-balance">
                    The Angel Lounge guest management portal — invitations, registration and lounge check-in for our esteemed partners.
                </p>
                <div className="mt-9 flex justify-center gap-3">
                    <Button asChild variant="gold" size="lg">
                        <Link href="/workspace">
                            Open the workspace <ArrowRightIcon />
                        </Link>
                    </Button>
                </div>
            </section>
            <section className="mx-auto grid max-w-4xl gap-4 sm:grid-cols-3">
                {[
                    { icon: UsersRoundIcon, title: 'Invite', text: 'Partners receive a personal registration link by email.' },
                    { icon: QrCodeIcon, title: 'Register', text: 'Each partner gets a tier-coloured QR pass.' },
                    { icon: SofaIcon, title: 'Welcome', text: 'Ushers scan passes at the lounge, even offline.' },
                ].map(({ icon: Icon, title, text }) => (
                    <div key={title} className="bg-card/70 rounded-2xl border p-6 backdrop-blur">
                        <Icon className="text-primary size-5" />
                        <p className="mt-4 font-medium">{title}</p>
                        <p className="text-muted-foreground mt-1 text-sm">{text}</p>
                    </div>
                ))}
            </section>
        </PublicLayout>
    );
}
