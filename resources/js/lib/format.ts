import { format, formatDistanceToNowStrict, isToday, isYesterday } from 'date-fns';

export function ago(iso?: string | null) {
    if (!iso) return '';
    return formatDistanceToNowStrict(new Date(iso), { addSuffix: true });
}

export function when(iso?: string | null) {
    if (!iso) return '—';
    const d = new Date(iso);
    if (isToday(d)) return `Today, ${format(d, 'h:mm a')}`;
    if (isYesterday(d)) return `Yesterday, ${format(d, 'h:mm a')}`;
    return format(d, 'MMM d, yyyy · h:mm a');
}

export function day(iso?: string | null) {
    if (!iso) return '—';
    return format(new Date(iso), 'MMM d, yyyy');
}

export function greeting() {
    const h = new Date().getHours();
    if (h < 12) return 'Good morning';
    if (h < 17) return 'Good afternoon';
    return 'Good evening';
}

export function pct(part: number, whole: number) {
    return whole > 0 ? Math.round((part / whole) * 100) : 0;
}
