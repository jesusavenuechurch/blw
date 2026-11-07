import { cn } from '@/lib/utils';

export function LiveDot({ className }: { className?: string }) {
    return (
        <span className={cn('relative inline-flex size-2', className)} aria-hidden>
            <span className="bg-success animate-pulse-ring absolute inset-0 rounded-full" />
            <span className="bg-success relative inline-flex size-2 rounded-full" />
        </span>
    );
}
