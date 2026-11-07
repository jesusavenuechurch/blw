import { useEffect, useRef, useState, type ReactNode } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    AlertTriangleIcon,
    FileUpIcon,
    HeartHandshakeIcon,
    HomeIcon,
    LogOutIcon,
    MailIcon,
    MenuIcon,
    MoonIcon,
    ScanLineIcon,
    SearchIcon,
    SmartphoneIcon,
    SofaIcon,
    SunIcon,
    UserCogIcon,
    UserPlusIcon,
    UsersRoundIcon,
    HourglassIcon,
} from 'lucide-react';
import { BrandWordmark } from '@/components/brand';
import { CommandPalette } from '@/components/workspace/command-palette';
import { TierDot } from '@/components/tier';
import { Button } from '@/components/ui/button';
import { Kbd } from '@/components/ui/kbd';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useFlash } from '@/hooks/use-flash';
import { useTheme } from '@/hooks/use-theme';
import { TIERS, TIER_META } from '@/lib/tiers';
import { cn, initials } from '@/lib/utils';
import type { SharedProps } from '@/types';

const NAV = [
    { label: 'Home', href: '/workspace', icon: HomeIcon, exact: true },
    { label: 'People', href: '/workspace/people', icon: UsersRoundIcon },
    { label: 'Check-ins', href: '/workspace/check-ins', icon: ScanLineIcon },
    { label: 'Outreach', href: '/workspace/outreach', icon: MailIcon },
    { label: 'Scanners', href: '/workspace/scanners', icon: SmartphoneIcon },
    { label: 'Import', href: '/workspace/import', icon: FileUpIcon },
    { label: 'Team', href: '/workspace/team', icon: UserCogIcon },
];

const VIEWS = [
    { label: 'Awaiting registration', view: 'awaiting', icon: HourglassIcon },
    { label: 'Email problems', view: 'email_failed', icon: AlertTriangleIcon },
    { label: 'Lounge guests', view: 'lounge', icon: SofaIcon },
    { label: 'Coming with spouse', view: 'spouse', icon: HeartHandshakeIcon },
];

export default function WorkspaceLayout({
    title,
    actions,
    children,
    className,
}: {
    title: string;
    actions?: ReactNode;
    children: ReactNode;
    className?: string;
}) {
    useFlash();
    const { auth } = usePage<SharedProps>().props;
    const { url } = usePage();
    const { dark, toggle } = useTheme();
    const [paletteOpen, setPaletteOpen] = useState(false);
    const [mobileOpen, setMobileOpen] = useState(false);
    const leader = useRef<number>(0);

    // ⌘K / Ctrl+K palette, "/" to search, and G-then-letter navigation.
    useEffect(() => {
        function onKey(e: KeyboardEvent) {
            const target = e.target as HTMLElement;
            const typing = target.closest('input, textarea, select, [contenteditable=true], [role=dialog]');
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                setPaletteOpen((o) => !o);
                return;
            }
            if (typing || e.metaKey || e.ctrlKey || e.altKey) return;
            if (e.key === '/') {
                e.preventDefault();
                setPaletteOpen(true);
                return;
            }
            if (e.key.toLowerCase() === 'g') {
                leader.current = Date.now();
                return;
            }
            if (Date.now() - leader.current < 900) {
                const map: Record<string, string> = { h: '/workspace', p: '/workspace/people', c: '/workspace/check-ins', o: '/workspace/outreach' };
                const dest = map[e.key.toLowerCase()];
                if (dest) router.visit(dest);
                leader.current = 0;
            }
        }
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, []);

    useEffect(() => setMobileOpen(false), [url]);

    const path = url.split('?')[0];
    const query = new URLSearchParams(url.split('?')[1] ?? '');

    const sidebar = (
        <div className="flex h-full flex-col">
            <div className="flex h-16 items-center px-5">
                <Link href="/workspace">
                    <BrandWordmark />
                </Link>
            </div>
            <div className="px-3">
                <button
                    onClick={() => setPaletteOpen(true)}
                    className="bg-background text-muted-foreground hover:text-foreground hover:border-foreground/15 flex h-9 w-full items-center gap-2 rounded-lg border px-3 text-sm shadow-xs transition-colors"
                >
                    <SearchIcon className="size-4" />
                    <span className="flex-1 text-left">Search…</span>
                    <Kbd>⌘K</Kbd>
                </button>
            </div>
            <nav className="scrollbar-thin mt-4 flex-1 space-y-6 overflow-y-auto px-3 pb-4">
                <div className="space-y-0.5">
                    {NAV.map((item) => {
                        const active = item.exact ? path === item.href : path.startsWith(item.href);
                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={cn(
                                    'group flex h-9 items-center gap-3 rounded-lg px-3 text-sm font-medium transition-colors',
                                    active ? 'bg-sidebar-accent text-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/60 hover:text-foreground',
                                )}
                            >
                                <item.icon className={cn('size-4', active ? 'text-primary' : 'opacity-70')} />
                                {item.label}
                            </Link>
                        );
                    })}
                </div>
                <div>
                    <p className="text-muted-foreground px-3 pb-1.5 text-[11px] font-medium tracking-wider uppercase">Views</p>
                    <div className="space-y-0.5">
                        {VIEWS.map((v) => {
                            const active = path === '/workspace/people' && query.get('view') === v.view;
                            return (
                                <Link
                                    key={v.view}
                                    href={`/workspace/people?view=${v.view}`}
                                    className={cn(
                                        'flex h-8 items-center gap-3 rounded-lg px-3 text-sm transition-colors',
                                        active ? 'bg-sidebar-accent text-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/60 hover:text-foreground',
                                    )}
                                >
                                    <v.icon className="size-4 opacity-70" />
                                    {v.label}
                                </Link>
                            );
                        })}
                    </div>
                </div>
                <div>
                    <p className="text-muted-foreground px-3 pb-1.5 text-[11px] font-medium tracking-wider uppercase">Lounges</p>
                    <div className="space-y-0.5">
                        {TIERS.map((t) => {
                            const active = path === '/workspace/people' && query.get('tier') === t;
                            return (
                                <Link
                                    key={t}
                                    href={`/workspace/people?tier=${t}`}
                                    className={cn(
                                        'flex h-8 items-center gap-3 rounded-lg px-3 text-sm transition-colors',
                                        active ? 'bg-sidebar-accent text-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/60 hover:text-foreground',
                                    )}
                                >
                                    <span className="grid size-4 place-items-center">
                                        <TierDot tier={t} className="size-2.5" />
                                    </span>
                                    {TIER_META[t].short}
                                </Link>
                            );
                        })}
                    </div>
                </div>
            </nav>
            <div className="border-sidebar-border border-t p-3">
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button className="hover:bg-sidebar-accent flex w-full items-center gap-3 rounded-lg p-2 text-left transition-colors">
                            <span className="bg-gold grid size-8 place-items-center rounded-full text-xs font-semibold text-[oklch(0.22_0.04_65)]">
                                {initials(auth.user?.name)}
                            </span>
                            <span className="min-w-0 flex-1">
                                <span className="block truncate text-sm font-medium">{auth.user?.name}</span>
                                <span className="text-muted-foreground block truncate text-xs">{auth.user?.email}</span>
                            </span>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start" side="top" className="w-56">
                        <DropdownMenuLabel>Signed in as {auth.user?.name}</DropdownMenuLabel>
                        <DropdownMenuItem onSelect={toggle}>
                            {dark ? <SunIcon /> : <MoonIcon />} {dark ? 'Light mode' : 'Dark mode'}
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem onSelect={() => router.post('/logout')}>
                            <LogOutIcon /> Sign out
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    );

    return (
        <TooltipProvider>
            <Head title={title} />
            <div className="bg-background min-h-svh">
                <aside className="bg-sidebar border-sidebar-border fixed inset-y-0 left-0 z-30 hidden w-64 border-r lg:block">{sidebar}</aside>
                <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
                    <SheetContent side="left" className="bg-sidebar w-72 p-0">
                        <SheetTitle className="sr-only">Navigation</SheetTitle>
                        {sidebar}
                    </SheetContent>
                </Sheet>

                <div className="lg:pl-64">
                    <header className="bg-background/80 sticky top-0 z-20 flex h-16 items-center gap-3 border-b px-4 backdrop-blur-xl sm:px-6">
                        <Button variant="ghost" size="icon" className="-ml-2 lg:hidden" onClick={() => setMobileOpen(true)} aria-label="Open navigation">
                            <MenuIcon />
                        </Button>
                        <h1 className="min-w-0 flex-1 truncate text-[15px] font-semibold tracking-tight">{title}</h1>
                        <div className="flex items-center gap-2">
                            {actions}
                            <Button variant="ghost" size="icon" className="lg:hidden" onClick={() => setPaletteOpen(true)} aria-label="Search">
                                <SearchIcon />
                            </Button>
                            <Button variant="ghost" size="icon" onClick={toggle} aria-label="Toggle theme" className="hidden sm:inline-flex">
                                {dark ? <SunIcon /> : <MoonIcon />}
                            </Button>
                            <Button asChild variant="gold" size="sm" className="hidden sm:inline-flex">
                                <Link href="/workspace/people?new=1" preserveState={path === '/workspace/people'}>
                                    <UserPlusIcon /> New partner
                                </Link>
                            </Button>
                        </div>
                    </header>
                    <main className={cn('mx-auto w-full max-w-[1500px] p-4 sm:p-6 lg:p-8', className)}>{children}</main>
                </div>
            </div>
            <CommandPalette open={paletteOpen} onOpenChange={setPaletteOpen} onToggleTheme={toggle} />
            <Toaster position="top-center" />
        </TooltipProvider>
    );
}
