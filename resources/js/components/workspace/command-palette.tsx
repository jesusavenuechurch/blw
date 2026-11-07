import { useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import {
    FileUpIcon,
    HomeIcon,
    MailIcon,
    MoonIcon,
    ScanLineIcon,
    SmartphoneIcon,
    UserPlusIcon,
    UsersRoundIcon,
    UserCogIcon,
} from 'lucide-react';
import { CommandDialog, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList, CommandSeparator, CommandShortcut } from '@/components/ui/command';
import { PartnerAvatar } from '@/components/partner-avatar';
import { TierBadge } from '@/components/tier';

interface Hit {
    id: number;
    title: string | null;
    full_name: string;
    email: string | null;
    tier: string;
    is_registered: boolean;
}

export function CommandPalette({ open, onOpenChange, onToggleTheme }: { open: boolean; onOpenChange: (o: boolean) => void; onToggleTheme: () => void }) {
    const [query, setQuery] = useState('');
    const [hits, setHits] = useState<Hit[]>([]);
    const timer = useRef<number | undefined>(undefined);

    useEffect(() => {
        if (!open) setQuery('');
    }, [open]);

    useEffect(() => {
        window.clearTimeout(timer.current);
        if (!query.trim()) {
            setHits([]);
            return;
        }
        const controller = new AbortController();
        timer.current = window.setTimeout(() => {
            fetch(`/workspace/search?q=${encodeURIComponent(query)}`, { headers: { Accept: 'application/json' }, signal: controller.signal })
                .then((r) => (r.ok ? r.json() : []))
                .then(setHits)
                .catch(() => {});
        }, 140);
        return () => controller.abort();
    }, [query]);

    function go(url: string) {
        onOpenChange(false);
        router.visit(url);
    }

    const nav = [
        { label: 'Home', icon: HomeIcon, url: '/workspace', key: 'G H' },
        { label: 'People', icon: UsersRoundIcon, url: '/workspace/people', key: 'G P' },
        { label: 'Check-ins', icon: ScanLineIcon, url: '/workspace/check-ins', key: 'G C' },
        { label: 'Outreach', icon: MailIcon, url: '/workspace/outreach', key: 'G O' },
        { label: 'Scanners', icon: SmartphoneIcon, url: '/workspace/scanners' },
        { label: 'Import', icon: FileUpIcon, url: '/workspace/import' },
        { label: 'Team', icon: UserCogIcon, url: '/workspace/team' },
    ];

    return (
        <CommandDialog open={open} onOpenChange={onOpenChange} shouldFilter={false} title="Search the workspace">
            <CommandInput placeholder="Search partners, or jump anywhere…" value={query} onValueChange={setQuery} />
            <CommandList>
                <CommandEmpty>No partners match “{query}”.</CommandEmpty>
                {hits.length > 0 && (
                    <CommandGroup heading="Partners">
                        {hits.map((h) => (
                            <CommandItem key={h.id} value={`p-${h.id}`} onSelect={() => go(`/workspace/people?partner=${h.id}`)}>
                                <PartnerAvatar name={h.full_name} tier={h.tier} className="size-7 text-[10px]" />
                                <span className="min-w-0 flex-1">
                                    <span className="block truncate font-medium">
                                        {h.title} {h.full_name}
                                    </span>
                                    <span className="text-muted-foreground block truncate text-xs">{h.email}</span>
                                </span>
                                <TierBadge tier={h.tier} short />
                            </CommandItem>
                        ))}
                    </CommandGroup>
                )}
                {!query.trim() && (
                    <>
                        <CommandGroup heading="Quick actions">
                            <CommandItem value="new" onSelect={() => go('/workspace/people?new=1')}>
                                <UserPlusIcon /> Add a partner
                            </CommandItem>
                            <CommandItem value="import" onSelect={() => go('/workspace/import')}>
                                <FileUpIcon /> Import a guest list
                            </CommandItem>
                            <CommandItem value="outreach" onSelect={() => go('/workspace/outreach')}>
                                <MailIcon /> Email a tier
                            </CommandItem>
                            <CommandItem
                                value="theme"
                                onSelect={() => {
                                    onToggleTheme();
                                    onOpenChange(false);
                                }}
                            >
                                <MoonIcon /> Toggle dark mode
                            </CommandItem>
                        </CommandGroup>
                        <CommandSeparator />
                        <CommandGroup heading="Go to">
                            {nav.map((n) => (
                                <CommandItem key={n.url} value={n.url} onSelect={() => go(n.url)}>
                                    <n.icon /> {n.label}
                                    {n.key && <CommandShortcut>{n.key}</CommandShortcut>}
                                </CommandItem>
                            ))}
                        </CommandGroup>
                    </>
                )}
            </CommandList>
        </CommandDialog>
    );
}
