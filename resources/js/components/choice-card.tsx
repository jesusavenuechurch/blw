import type { ReactNode } from 'react';
import { CheckIcon } from 'lucide-react';
import { cn } from '@/lib/utils';

/** A large yes/no toggle card — friendlier than a bare checkbox. */
export function ChoiceCard({
    checked,
    onChange,
    title,
    description,
    icon,
}: {
    checked: boolean;
    onChange: (v: boolean) => void;
    title: ReactNode;
    description?: ReactNode;
    icon?: ReactNode;
}) {
    return (
        <button
            type="button"
            role="switch"
            aria-checked={checked}
            onClick={() => onChange(!checked)}
            className={cn(
                'group focus-visible:ring-ring/50 flex w-full items-start gap-4 rounded-xl border p-4 text-left transition-all outline-none focus-visible:ring-[3px]',
                checked ? 'border-primary/60 bg-primary/[0.06] shadow-sm' : 'bg-card hover:border-foreground/20 hover:bg-accent/40',
            )}
        >
            {icon && (
                <span className={cn('grid size-10 shrink-0 place-items-center rounded-lg transition-colors', checked ? 'bg-gold text-[oklch(0.22_0.04_65)]' : 'bg-muted text-muted-foreground')}>
                    {icon}
                </span>
            )}
            <span className="min-w-0 flex-1">
                <span className="block font-medium">{title}</span>
                {description && <span className="text-muted-foreground mt-0.5 block text-sm">{description}</span>}
            </span>
            <span
                className={cn(
                    'mt-0.5 grid size-5 shrink-0 place-items-center rounded-full border transition-all',
                    checked ? 'bg-primary border-primary text-primary-foreground scale-100' : 'border-input',
                )}
            >
                <CheckIcon className={cn('size-3 transition-opacity', checked ? 'opacity-100' : 'opacity-0')} strokeWidth={3} />
            </span>
        </button>
    );
}
