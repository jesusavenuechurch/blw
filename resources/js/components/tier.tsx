import { tierMeta } from '@/lib/tiers';
import { cn } from '@/lib/utils';

export function TierDot({ tier, className }: { tier: string | null | undefined; className?: string }) {
    return <span className={cn('inline-block size-2 shrink-0 rounded-full', className)} style={{ background: tierMeta(tier).color }} />;
}

export function TierBadge({ tier, className, short }: { tier: string | null | undefined; className?: string; short?: boolean }) {
    const meta = tierMeta(tier);
    return (
        <span
            className={cn('inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-xs font-medium whitespace-nowrap', className)}
            style={{
                borderColor: `color-mix(in oklch, ${meta.color} 35%, transparent)`,
                background: `color-mix(in oklch, ${meta.color} 10%, transparent)`,
            }}
        >
            <TierDot tier={tier} />
            {short ? meta.short : meta.label}
        </span>
    );
}
