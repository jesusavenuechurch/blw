import { tierMeta } from '@/lib/tiers';
import { cn, initials } from '@/lib/utils';

export function PartnerAvatar({ name, tier, photo, className }: { name: string; tier?: string | null; photo?: string | null; className?: string }) {
    const color = tierMeta(tier).color;
    return (
        <span
            className={cn('relative grid size-9 shrink-0 place-items-center rounded-full text-[0.8rem] font-semibold tracking-wide', className)}
            style={{
                background: `color-mix(in oklch, ${color} 14%, var(--card))`,
                color: `color-mix(in oklch, ${color} 75%, var(--foreground))`,
                boxShadow: `inset 0 0 0 1.5px color-mix(in oklch, ${color} 45%, transparent)`,
            }}
        >
            {photo ? <img src={photo} alt="" loading="lazy" className="absolute inset-[2px] size-[calc(100%-4px)] rounded-full object-cover" /> : initials(name)}
        </span>
    );
}
