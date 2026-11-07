import { BadgeCheckIcon, HeartHandshakeIcon, ShieldAlertIcon } from 'lucide-react';
import PublicLayout from '@/layouts/public-layout';
import { TierBadge } from '@/components/tier';
import { PartnerAvatar } from '@/components/partner-avatar';
import { tierMeta } from '@/lib/tiers';

interface Props {
    partner: {
        title: string | null;
        full_name: string;
        tier: string;
        is_registered: boolean;
        coming_with_spouse: boolean;
        spouse_title: string | null;
        spouse_name: string | null;
    };
}

export default function Verify({ partner }: Props) {
    const color = tierMeta(partner.tier).color;
    return (
        <PublicLayout title="Partner verification">
            <div className="animate-rise mx-auto max-w-md pt-10">
                <div
                    className="bg-card overflow-hidden rounded-3xl border p-8 text-center shadow-xl shadow-black/5"
                    style={{ backgroundImage: `radial-gradient(24rem 12rem at 50% -10%, color-mix(in oklch, ${color} 25%, transparent), transparent 70%)` }}
                >
                    {partner.is_registered ? (
                        <span className="bg-success/12 text-success inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium">
                            <BadgeCheckIcon className="size-4" /> Verified partner
                        </span>
                    ) : (
                        <span className="bg-warning/18 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium">
                            <ShieldAlertIcon className="size-4" /> Registration not completed
                        </span>
                    )}
                    <PartnerAvatar name={partner.full_name} tier={partner.tier} className="mx-auto mt-8 size-20 text-2xl" />
                    <h1 className="mt-5 font-serif text-4xl leading-tight tracking-tight">
                        {partner.title} {partner.full_name}
                    </h1>
                    <div className="mt-4 flex justify-center">
                        <TierBadge tier={partner.tier} className="bg-card px-3 py-1 text-sm" />
                    </div>
                    {partner.coming_with_spouse && partner.spouse_name && (
                        <div className="bg-muted/60 mt-8 flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm">
                            <HeartHandshakeIcon className="text-primary size-4" />
                            Accompanied by {partner.spouse_title} {partner.spouse_name}
                        </div>
                    )}
                </div>
            </div>
        </PublicLayout>
    );
}
