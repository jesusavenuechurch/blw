import { cn } from '@/lib/utils';

/** Angel Lounges monogram: a gold "A" with wing strokes. */
export function BrandMark({ className }: { className?: string }) {
    return (
        <span className={cn('bg-gold relative grid size-8 place-items-center rounded-lg shadow-sm ring-1 shadow-gold-2/30 ring-black/5', className)}>
            <svg viewBox="0 0 32 32" className="size-[70%] text-[oklch(0.22_0.04_65)]" fill="none" stroke="currentColor" strokeWidth={2.2} strokeLinecap="round" strokeLinejoin="round" aria-hidden>
                <path d="M10 24 16 8l6 16" />
                <path d="M12.4 18.5h7.2" />
                <path d="M4 13c3 0 5 1.2 6.6 3.6M28 13c-3 0-5 1.2-6.6 3.6" opacity={0.7} />
            </svg>
        </span>
    );
}

export function BrandWordmark({ className, sub = true }: { className?: string; sub?: boolean }) {
    return (
        <span className={cn('flex items-center gap-2.5', className)}>
            <BrandMark />
            <span className="flex flex-col leading-none">
                <span className="font-serif text-[1.35rem] tracking-tight">Angel Lounges</span>
                {sub && <span className="text-muted-foreground mt-0.5 text-[10px] font-medium tracking-[0.2em] uppercase">IPPC 2025</span>}
            </span>
        </span>
    );
}
