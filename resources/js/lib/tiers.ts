import type { EmailState, Stage, Tier } from '@/types';

export const TIERS: Tier[] = ['ruby', 'silver', 'gold', 'diamond', 'as_one_man', 'top_individual'];

export const TIER_META: Record<Tier, { label: string; short: string; color: string }> = {
    ruby: { label: 'Ruby', short: 'Ruby', color: 'var(--tier-ruby)' },
    silver: { label: 'Silver', short: 'Silver', color: 'var(--tier-silver)' },
    gold: { label: 'Gold', short: 'Gold', color: 'var(--tier-gold)' },
    diamond: { label: 'Diamond', short: 'Diamond', color: 'var(--tier-diamond)' },
    as_one_man: { label: 'As One Man', short: 'As One Man', color: 'var(--tier-as_one_man)' },
    top_individual: { label: 'Top Individual Partner', short: 'Top Individual', color: 'var(--tier-top_individual)' },
};

export function tierMeta(tier: string | null | undefined) {
    return TIER_META[tier as Tier] ?? { label: tier ? String(tier) : 'No tier', short: tier ? String(tier) : 'No tier', color: 'var(--muted-foreground)' };
}

export const STAGES: Stage[] = ['invited', 'delivered', 'registered', 'checked_in'];

export const STAGE_META: Record<Stage, { label: string; hint: string; color: string }> = {
    invited: { label: 'Invited', hint: 'On the list, invitation not delivered yet', color: 'var(--muted-foreground)' },
    delivered: { label: 'Invitation delivered', hint: 'Email delivered, waiting for them to register', color: 'var(--info)' },
    registered: { label: 'Registered', hint: 'Completed registration and has a QR pass', color: 'var(--primary)' },
    checked_in: { label: 'Checked in', hint: 'Arrived at the lounge', color: 'var(--success)' },
};

export const EMAIL_META: Record<EmailState, { label: string; variant: 'success' | 'warning' | 'destructive' | 'secondary' }> = {
    sent: { label: 'Email sent', variant: 'success' },
    pending: { label: 'Email pending', variant: 'warning' },
    failed: { label: 'Email failed', variant: 'destructive' },
    none: { label: 'Not emailed', variant: 'secondary' },
};
