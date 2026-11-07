import { useEffect, useMemo, useRef, useState } from 'react';
import { router, usePage, usePoll } from '@inertiajs/react';
import {
    AlertTriangleIcon,
    CheckIcon,
    Columns3Icon,
    FilterIcon,
    HeartHandshakeIcon,
    LayoutGridIcon,
    ListIcon,
    Loader2Icon,
    PlaneIcon,
    QrCodeIcon,
    SearchIcon,
    SendIcon,
    SofaIcon,
    Trash2Icon,
    UsersRoundIcon,
    XIcon,
} from 'lucide-react';
import WorkspaceLayout from '@/layouts/workspace-layout';
import { PartnerAvatar } from '@/components/partner-avatar';
import { PartnerForm } from '@/components/people/partner-form';
import { PartnerPanel, StageBadge } from '@/components/people/partner-panel';
import { TierBadge, TierDot } from '@/components/tier';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Kbd } from '@/components/ui/kbd';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { EMAIL_META, STAGES, STAGE_META, TIERS, TIER_META, tierMeta } from '@/lib/tiers';
import { ago } from '@/lib/format';
import { cn, pluralize } from '@/lib/utils';
import type { EmailState, FormOptions, PartnerCard, PartnerDetail, Stage, Tier } from '@/types';

interface Props {
    partners: PartnerCard[];
    selected: PartnerDetail | null;
    options: FormOptions;
}

type Layout = 'board' | 'cards' | 'list';
type GroupBy = 'stage' | 'tier';
type View = 'awaiting' | 'email_failed' | 'lounge' | 'spouse' | null;

const VIEW_LABEL: Record<Exclude<View, null>, string> = {
    awaiting: 'Awaiting registration',
    email_failed: 'Email problems',
    lounge: 'Lounge guests',
    spouse: 'Coming with spouse',
};

function readUrl(url: string) {
    const q = new URLSearchParams(url.split('?')[1] ?? '');
    return {
        tier: q.get('tier'),
        stage: q.get('stage'),
        view: q.get('view') as View,
        group: q.get('group') as GroupBy | null,
        partner: q.get('partner'),
        isNew: q.get('new') === '1',
    };
}

function loadLayout(): Layout {
    try {
        const v = localStorage.getItem('people.layout');
        if (v === 'board' || v === 'cards' || v === 'list') return v;
    } catch {
        /* ignore */
    }
    return 'board';
}

export default function People({ partners, selected, options }: Props) {
    const { url } = usePage();
    const initial = readUrl(url);

    const [layout, setLayout] = useState<Layout>(loadLayout);
    const [groupBy, setGroupBy] = useState<GroupBy>(initial.group ?? 'stage');
    const [search, setSearch] = useState('');
    const [tiers, setTiers] = useState<Set<string>>(new Set(initial.tier ? [initial.tier] : []));
    const [stages, setStages] = useState<Set<Stage>>(new Set(initial.stage ? [initial.stage as Stage] : []));
    const [emails, setEmails] = useState<Set<EmailState>>(new Set());
    const [view, setView] = useState<View>(initial.view);
    const [picked, setPicked] = useState<Set<number>>(new Set());
    const [panelId, setPanelId] = useState<number | null>(initial.partner ? Number(initial.partner) : null);
    const [creating, setCreating] = useState(initial.isNew);
    const [loadingPanel, setLoadingPanel] = useState(false);
    const [bulkBusy, setBulkBusy] = useState<string | null>(null);
    const [confirmDelete, setConfirmDelete] = useState(false);
    const searchRef = useRef<HTMLInputElement>(null);

    // Apply filters when navigating here from the sidebar, palette or home (?view=, ?tier=, ?stage=, ?new=).
    const sig = `${initial.tier}|${initial.stage}|${initial.view}|${initial.group}`;
    const lastSig = useRef(sig);
    useEffect(() => {
        const u = readUrl(url);
        if (sig !== lastSig.current) {
            lastSig.current = sig;
            setTiers(new Set(u.tier ? [u.tier] : []));
            setStages(new Set(u.stage ? [u.stage as Stage] : []));
            setView(u.view);
            setEmails(new Set());
            setSearch('');
            if (u.group) setGroupBy(u.group);
        }
        if (u.isNew) setCreating(true);
        setPanelId(u.partner ? Number(u.partner) : null);
    }, [url, sig]);

    useEffect(() => {
        try {
            localStorage.setItem('people.layout', layout);
        } catch {
            /* ignore */
        }
    }, [layout]);

    usePoll(10000, { only: ['partners', 'selected'] });

    // "F" focuses the search box on this page; Esc clears the selection.
    useEffect(() => {
        function onKey(e: KeyboardEvent) {
            if (e.key === 'Escape' && picked.size > 0 && !panelId) setPicked(new Set());
            if (e.key === 'f' && !(e.target as HTMLElement).closest('input,textarea,[role=dialog]')) {
                e.preventDefault();
                searchRef.current?.focus();
            }
        }
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [picked.size, panelId]);

    const filtered = useMemo(() => {
        const q = search.trim().toLowerCase();
        return partners.filter((p) => {
            if (tiers.size && !tiers.has(p.tier)) return false;
            if (stages.size && !stages.has(p.stage)) return false;
            if (emails.size && !emails.has(p.email_state)) return false;
            if (view === 'awaiting' && p.is_registered) return false;
            if (view === 'email_failed' && p.email_state !== 'failed') return false;
            if (view === 'lounge' && !p.will_be_at_exhibition) return false;
            if (view === 'spouse' && !p.coming_with_spouse) return false;
            if (q) {
                const hay = [p.full_name, p.email, p.phone, p.church, p.zone, p.region, p.designation, p.spouse_name].filter(Boolean).join(' ').toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        });
    }, [partners, search, tiers, stages, emails, view]);

    const columns = useMemo(() => {
        if (groupBy === 'stage') {
            return STAGES.map((s) => ({ key: s, label: STAGE_META[s].label, hint: STAGE_META[s].hint, color: STAGE_META[s].color, items: filtered.filter((p) => p.stage === s) }));
        }
        const known = TIERS.map((t) => ({ key: t, label: TIER_META[t].label, hint: '', color: TIER_META[t].color, items: filtered.filter((p) => p.tier === t) }));
        const other = filtered.filter((p) => !TIERS.includes(p.tier as Tier));
        return other.length ? [...known, { key: 'other', label: 'Other', hint: '', color: 'var(--muted-foreground)', items: other }] : known;
    }, [filtered, groupBy]);

    function openPartner(id: number) {
        setPanelId(id);
        setLoadingPanel(true);
        router.get(
            '/workspace/people',
            { ...currentParams(), partner: id },
            { only: ['selected'], preserveState: true, preserveScroll: true, replace: true, onFinish: () => setLoadingPanel(false) },
        );
    }

    function closePartner() {
        setPanelId(null);
        router.get('/workspace/people', currentParams(), { only: ['selected'], preserveState: true, preserveScroll: true, replace: true });
    }

    function closeCreate() {
        setCreating(false);
        if (readUrl(url).isNew) router.get('/workspace/people', currentParams(), { only: ['selected'], preserveState: true, preserveScroll: true, replace: true });
    }

    function currentParams() {
        const u = readUrl(url);
        return Object.fromEntries(
            Object.entries({ tier: u.tier, stage: u.stage, view: u.view, group: u.group }).filter(([, v]) => v),
        ) as Record<string, string>;
    }

    function toggle<T>(set: Set<T>, value: T, setter: (s: Set<T>) => void) {
        const next = new Set(set);
        next.has(value) ? next.delete(value) : next.add(value);
        setter(next);
    }

    function togglePick(id: number) {
        toggle(picked, id, setPicked);
    }

    function bulk(action: 'qr' | 'resend' | 'delete') {
        setBulkBusy(action);
        router.post(
            '/workspace/people/bulk',
            { action, ids: [...picked] },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => setPicked(new Set()),
                onFinish: () => {
                    setBulkBusy(null);
                    setConfirmDelete(false);
                },
            },
        );
    }

    const activeFilters = tiers.size + stages.size + emails.size + (view ? 1 : 0);
    const panelPartner = selected && selected.id === panelId ? selected : null;

    return (
        <WorkspaceLayout title="People" className="flex flex-col">
            {/* Toolbar */}
            <div className="mb-5 flex flex-wrap items-center gap-2">
                <div className="relative w-full sm:w-72">
                    <SearchIcon className="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                    <Input ref={searchRef} value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Find someone…" className="h-9 pr-9 pl-9" />
                    {search ? (
                        <button onClick={() => setSearch('')} className="text-muted-foreground hover:text-foreground absolute top-1/2 right-2.5 -translate-y-1/2" aria-label="Clear search">
                            <XIcon className="size-4" />
                        </button>
                    ) : (
                        <Kbd className="absolute top-1/2 right-2.5 -translate-y-1/2">F</Kbd>
                    )}
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline" size="sm" className="h-9">
                            <FilterIcon /> Filter
                            {activeFilters > 0 && <span className="bg-primary text-primary-foreground grid size-5 place-items-center rounded-full text-[10px]">{activeFilters}</span>}
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start" className="w-60">
                        <DropdownMenuLabel>Lounge</DropdownMenuLabel>
                        {TIERS.map((t) => (
                            <DropdownMenuCheckboxItem key={t} checked={tiers.has(t)} onCheckedChange={() => toggle(tiers, t, setTiers)} onSelect={(e) => e.preventDefault()}>
                                <TierDot tier={t} /> {TIER_META[t].label}
                            </DropdownMenuCheckboxItem>
                        ))}
                        <DropdownMenuSeparator />
                        <DropdownMenuLabel>Journey</DropdownMenuLabel>
                        {STAGES.map((s) => (
                            <DropdownMenuCheckboxItem key={s} checked={stages.has(s)} onCheckedChange={() => toggle(stages, s, setStages)} onSelect={(e) => e.preventDefault()}>
                                {STAGE_META[s].label}
                            </DropdownMenuCheckboxItem>
                        ))}
                        <DropdownMenuSeparator />
                        <DropdownMenuLabel>Email</DropdownMenuLabel>
                        {(['sent', 'pending', 'failed', 'none'] as EmailState[]).map((s) => (
                            <DropdownMenuCheckboxItem key={s} checked={emails.has(s)} onCheckedChange={() => toggle(emails, s, setEmails)} onSelect={(e) => e.preventDefault()}>
                                {EMAIL_META[s].label}
                            </DropdownMenuCheckboxItem>
                        ))}
                    </DropdownMenuContent>
                </DropdownMenu>

                {/* Active filter chips */}
                {view && <Chip onClear={() => setView(null)}>{VIEW_LABEL[view]}</Chip>}
                {[...tiers].map((t) => (
                    <Chip key={t} onClear={() => toggle(tiers, t, setTiers)}>
                        <TierDot tier={t} /> {tierMeta(t).short}
                    </Chip>
                ))}
                {[...stages].map((s) => (
                    <Chip key={s} onClear={() => toggle(stages, s, setStages)}>
                        {STAGE_META[s].label}
                    </Chip>
                ))}
                {[...emails].map((s) => (
                    <Chip key={s} onClear={() => toggle(emails, s, setEmails)}>
                        {EMAIL_META[s].label}
                    </Chip>
                ))}
                {activeFilters > 1 && (
                    <button
                        className="text-muted-foreground hover:text-foreground text-xs"
                        onClick={() => {
                            setTiers(new Set());
                            setStages(new Set());
                            setEmails(new Set());
                            setView(null);
                        }}
                    >
                        Clear all
                    </button>
                )}

                <div className="ml-auto flex items-center gap-2">
                    <span className="text-muted-foreground hidden text-sm tabular-nums md:inline">
                        {filtered.length === partners.length ? pluralize(partners.length, 'partner') : `${filtered.length} of ${partners.length}`}
                    </span>
                    {layout === 'board' && (
                        <ToggleGroup type="single" value={groupBy} onValueChange={(v) => v && setGroupBy(v as GroupBy)} aria-label="Group by">
                            <ToggleGroupItem value="stage">Journey</ToggleGroupItem>
                            <ToggleGroupItem value="tier">Lounge</ToggleGroupItem>
                        </ToggleGroup>
                    )}
                    <ToggleGroup type="single" value={layout} onValueChange={(v) => v && setLayout(v as Layout)} aria-label="Layout">
                        <LayoutItem value="board" label="Board" icon={<Columns3Icon />} />
                        <LayoutItem value="cards" label="Cards" icon={<LayoutGridIcon />} />
                        <LayoutItem value="list" label="List" icon={<ListIcon />} />
                    </ToggleGroup>
                </div>
            </div>

            {partners.length === 0 ? (
                <EmptyState />
            ) : layout === 'board' ? (
                <div className="scrollbar-thin -mx-4 flex flex-1 gap-4 overflow-x-auto px-4 pb-24 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                    {columns.map((col) => (
                        <BoardColumn
                            key={col.key}
                            label={col.label}
                            hint={col.hint}
                            color={col.color}
                            items={col.items}
                            picked={picked}
                            showTier={groupBy === 'stage'}
                            onOpen={openPartner}
                            onPick={togglePick}
                            onPickAll={(ids, on) => {
                                const next = new Set(picked);
                                ids.forEach((id) => (on ? next.add(id) : next.delete(id)));
                                setPicked(next);
                            }}
                        />
                    ))}
                </div>
            ) : layout === 'cards' ? (
                <div className="grid gap-3 pb-24 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    {filtered.map((p) => (
                        <PersonCard key={p.id} p={p} picked={picked.has(p.id)} onOpen={openPartner} onPick={togglePick} rich />
                    ))}
                    {filtered.length === 0 && <NoMatches />}
                </div>
            ) : (
                <ListView items={filtered} picked={picked} onOpen={openPartner} onPick={togglePick} setPicked={setPicked} />
            )}

            {/* Bulk bar */}
            <div
                className={cn(
                    'fixed inset-x-0 bottom-6 z-40 flex justify-center px-4 transition-all duration-300 lg:pl-64',
                    picked.size > 0 ? 'translate-y-0 opacity-100' : 'pointer-events-none translate-y-6 opacity-0',
                )}
            >
                <div className="bg-foreground text-background flex items-center gap-1 rounded-full py-1.5 pr-1.5 pl-4 shadow-2xl">
                    <span className="mr-2 text-sm font-medium tabular-nums">{picked.size} selected</span>
                    <BulkButton onClick={() => bulk('qr')} busy={bulkBusy === 'qr'} icon={<QrCodeIcon />}>
                        QR codes
                    </BulkButton>
                    <BulkButton onClick={() => bulk('resend')} busy={bulkBusy === 'resend'} icon={<SendIcon />}>
                        Resend emails
                    </BulkButton>
                    <BulkButton onClick={() => setConfirmDelete(true)} icon={<Trash2Icon />} danger>
                        Remove
                    </BulkButton>
                    <button onClick={() => setPicked(new Set())} className="hover:bg-background/15 ml-1 grid size-8 place-items-center rounded-full" aria-label="Clear selection">
                        <XIcon className="size-4" />
                    </button>
                </div>
            </div>

            <AlertDialog open={confirmDelete} onOpenChange={setConfirmDelete}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Remove {pluralize(picked.size, 'partner')}?</AlertDialogTitle>
                        <AlertDialogDescription>Their details, QR passes and check-in history will be deleted. This can’t be undone.</AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction variant="destructive" onClick={() => bulk('delete')}>
                            Remove
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <PartnerPanel open={panelId !== null} partner={panelPartner} loading={loadingPanel} options={options} onClose={closePartner} />

            <Sheet open={creating} onOpenChange={(o) => !o && closeCreate()}>
                <SheetContent className="w-full gap-0 p-0 sm:max-w-[560px]">
                    <SheetHeader className="border-b px-6 pt-8 pb-5">
                        <SheetTitle className="font-serif text-3xl font-normal tracking-tight">New partner</SheetTitle>
                        <SheetDescription>Add someone to the guest list. They’ll receive their personal registration link by email.</SheetDescription>
                    </SheetHeader>
                    <div className="flex min-h-0 flex-1 flex-col pt-6">
                        <PartnerForm options={options} onDone={() => setCreating(false)} onCancel={closeCreate} />
                    </div>
                </SheetContent>
            </Sheet>
        </WorkspaceLayout>
    );
}

/* ——— Pieces ——— */

function LayoutItem({ value, label, icon }: { value: Layout; label: string; icon: React.ReactNode }) {
    return (
        <Tooltip>
            <TooltipTrigger asChild>
                <ToggleGroupItem value={value} aria-label={label} className="px-2">
                    {icon}
                </ToggleGroupItem>
            </TooltipTrigger>
            <TooltipContent>{label}</TooltipContent>
        </Tooltip>
    );
}

function Chip({ children, onClear }: { children: React.ReactNode; onClear: () => void }) {
    return (
        <span className="bg-accent text-accent-foreground inline-flex h-7 items-center gap-1.5 rounded-full pr-1 pl-2.5 text-xs font-medium">
            {children}
            <button onClick={onClear} className="hover:bg-foreground/10 grid size-5 place-items-center rounded-full" aria-label="Remove filter">
                <XIcon className="size-3" />
            </button>
        </span>
    );
}

function BulkButton({ children, icon, onClick, busy, danger }: { children: React.ReactNode; icon: React.ReactNode; onClick: () => void; busy?: boolean; danger?: boolean }) {
    return (
        <button
            onClick={onClick}
            disabled={busy}
            className={cn(
                'hover:bg-background/15 flex h-8 items-center gap-1.5 rounded-full px-3 text-sm transition-colors disabled:opacity-60 [&_svg]:size-4',
                danger && 'text-[oklch(0.78_0.15_25)]',
            )}
        >
            {busy ? <Loader2Icon className="animate-spin" /> : icon}
            <span className="hidden sm:inline">{children}</span>
        </button>
    );
}

const COLUMN_PAGE = 50;

function BoardColumn({
    label,
    hint,
    color,
    items,
    picked,
    showTier,
    onOpen,
    onPick,
    onPickAll,
}: {
    label: string;
    hint: string;
    color: string;
    items: PartnerCard[];
    picked: Set<number>;
    showTier: boolean;
    onOpen: (id: number) => void;
    onPick: (id: number) => void;
    onPickAll: (ids: number[], on: boolean) => void;
}) {
    const [limit, setLimit] = useState(COLUMN_PAGE);
    const allPicked = items.length > 0 && items.every((p) => picked.has(p.id));

    return (
        <section className="bg-muted/45 flex w-[300px] shrink-0 flex-col rounded-2xl border border-transparent dark:border-white/5">
            <header className="group/col flex items-center gap-2 px-3.5 pt-3.5 pb-2.5">
                <span className="size-2 rounded-full" style={{ background: color }} />
                <h3 className="text-sm font-semibold">{label}</h3>
                <span className="text-muted-foreground text-sm tabular-nums">{items.length}</span>
                {items.length > 0 && (
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <span className="ml-auto opacity-0 transition-opacity group-hover/col:opacity-100 data-[on=true]:opacity-100" data-on={allPicked}>
                                <Checkbox checked={allPicked} onCheckedChange={(v) => onPickAll(items.map((p) => p.id), v === true)} aria-label={`Select all in ${label}`} />
                            </span>
                        </TooltipTrigger>
                        <TooltipContent>Select all {items.length}</TooltipContent>
                    </Tooltip>
                )}
            </header>
            {hint && <p className="text-muted-foreground -mt-1 px-3.5 pb-2.5 text-xs">{hint}</p>}
            <div className="scrollbar-thin flex max-h-[calc(100svh-15rem)] flex-col gap-2 overflow-y-auto px-2 pb-2">
                {items.slice(0, limit).map((p) => (
                    <PersonCard key={p.id} p={p} picked={picked.has(p.id)} onOpen={onOpen} onPick={onPick} showTier={showTier} />
                ))}
                {items.length > limit && (
                    <Button variant="ghost" size="sm" onClick={() => setLimit((l) => l + COLUMN_PAGE)} className="text-muted-foreground">
                        Show {Math.min(COLUMN_PAGE, items.length - limit)} more
                    </Button>
                )}
                {items.length === 0 && <p className="text-muted-foreground rounded-xl border border-dashed py-8 text-center text-xs">Nobody here</p>}
            </div>
        </section>
    );
}

function PersonCard({
    p,
    picked,
    onOpen,
    onPick,
    showTier = true,
    rich = false,
}: {
    p: PartnerCard;
    picked: boolean;
    onOpen: (id: number) => void;
    onPick: (id: number) => void;
    showTier?: boolean;
    rich?: boolean;
}) {
    return (
        <article
            onClick={() => onOpen(p.id)}
            onKeyDown={(e) => e.key === 'Enter' && onOpen(p.id)}
            tabIndex={0}
            className={cn(
                'group bg-card focus-visible:ring-ring/50 relative cursor-pointer rounded-xl border p-3 shadow-xs transition-all outline-none hover:-translate-y-px hover:shadow-md focus-visible:ring-[3px]',
                picked && 'border-primary/60 ring-primary/30 ring-2',
            )}
        >
            <div className="flex items-start gap-3">
                <div className="relative">
                    <PartnerAvatar name={p.full_name} tier={p.tier} photo={p.photo_url} />
                    <span
                        onClick={(e) => {
                            e.stopPropagation();
                            onPick(p.id);
                        }}
                        className={cn(
                            'bg-card absolute -top-1 -left-1 grid size-[18px] place-items-center rounded-[5px] border shadow-sm transition-all',
                            picked ? 'bg-primary border-primary text-primary-foreground scale-100 opacity-100' : 'scale-90 opacity-0 group-hover:scale-100 group-hover:opacity-100',
                        )}
                        role="checkbox"
                        aria-checked={picked}
                        aria-label={`Select ${p.full_name}`}
                    >
                        {picked && <CheckIcon className="size-3" strokeWidth={3} />}
                    </span>
                </div>
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-medium">
                        {p.title && <span className="text-muted-foreground font-normal">{p.title} </span>}
                        {p.full_name}
                    </p>
                    <p className="text-muted-foreground truncate text-xs">{p.church || p.zone || p.designation || p.email || '—'}</p>
                </div>
            </div>
            <div className="mt-3 flex items-center gap-1.5">
                {showTier && <TierBadge tier={p.tier} short className="text-[11px]" />}
                {rich && <StageBadge stage={p.stage} className="text-[11px]" />}
                <span className="text-muted-foreground ml-auto flex items-center gap-1.5 [&_svg]:size-3.5">
                    {p.email_state === 'failed' && (
                        <Hint label="Email failed">
                            <AlertTriangleIcon className="text-destructive" />
                        </Hint>
                    )}
                    {p.email_state === 'pending' && (
                        <Hint label="Email pending">
                            <SendIcon className="text-warning" />
                        </Hint>
                    )}
                    {p.will_be_at_exhibition && (
                        <Hint label="Lounge guest">
                            <SofaIcon />
                        </Hint>
                    )}
                    {p.will_attend_ippc && !p.will_be_at_exhibition && (
                        <Hint label="Attending IPPC">
                            <PlaneIcon />
                        </Hint>
                    )}
                    {p.coming_with_spouse && (
                        <Hint label={p.spouse_name ? `With ${p.spouse_name}` : 'With spouse'}>
                            <HeartHandshakeIcon />
                        </Hint>
                    )}
                    {p.has_qr && (
                        <Hint label="Has QR pass">
                            <QrCodeIcon />
                        </Hint>
                    )}
                </span>
            </div>
            {rich && (
                <p className="text-muted-foreground mt-3 border-t pt-2.5 text-[11px]">
                    {p.ror_copies_sponsored > 0 ? `${p.ror_copies_sponsored.toLocaleString()} ROR copies · ` : ''}updated {ago(p.updated_at)}
                </p>
            )}
        </article>
    );
}

function Hint({ label, children }: { label: string; children: React.ReactNode }) {
    return (
        <Tooltip>
            <TooltipTrigger asChild>
                <span>{children}</span>
            </TooltipTrigger>
            <TooltipContent>{label}</TooltipContent>
        </Tooltip>
    );
}

function ListView({
    items,
    picked,
    onOpen,
    onPick,
    setPicked,
}: {
    items: PartnerCard[];
    picked: Set<number>;
    onOpen: (id: number) => void;
    onPick: (id: number) => void;
    setPicked: (s: Set<number>) => void;
}) {
    const [limit, setLimit] = useState(100);
    const all = items.length > 0 && items.every((p) => picked.has(p.id));
    return (
        <div className="bg-card mb-24 overflow-hidden rounded-xl border shadow-xs">
            <div className="text-muted-foreground grid grid-cols-[28px_minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_110px] items-center gap-3 border-b px-4 py-2.5 text-xs font-medium md:grid-cols-[28px_minmax(0,2fr)_minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)_110px]">
                <Checkbox checked={all} onCheckedChange={(v) => setPicked(v === true ? new Set(items.map((p) => p.id)) : new Set())} aria-label="Select all" />
                <span>Partner</span>
                <span className="hidden md:block">Church</span>
                <span>Lounge</span>
                <span>Journey</span>
                <span className="text-right">Updated</span>
            </div>
            {items.slice(0, limit).map((p) => (
                <div
                    key={p.id}
                    onClick={() => onOpen(p.id)}
                    className={cn(
                        'hover:bg-accent/40 grid cursor-pointer grid-cols-[28px_minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_110px] items-center gap-3 border-b px-4 py-2.5 text-sm last:border-0 md:grid-cols-[28px_minmax(0,2fr)_minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)_110px]',
                        picked.has(p.id) && 'bg-primary/[0.05]',
                    )}
                >
                    <span onClick={(e) => e.stopPropagation()}>
                        <Checkbox checked={picked.has(p.id)} onCheckedChange={() => onPick(p.id)} aria-label={`Select ${p.full_name}`} />
                    </span>
                    <span className="flex min-w-0 items-center gap-3">
                        <PartnerAvatar name={p.full_name} tier={p.tier} photo={p.photo_url} className="size-7 text-[10px]" />
                        <span className="min-w-0">
                            <span className="block truncate font-medium">
                                {p.title} {p.full_name}
                            </span>
                            <span className="text-muted-foreground block truncate text-xs">{p.email}</span>
                        </span>
                    </span>
                    <span className="text-muted-foreground hidden truncate md:block">{p.church || '—'}</span>
                    <span>
                        <TierBadge tier={p.tier} short />
                    </span>
                    <span>
                        <StageBadge stage={p.stage} />
                    </span>
                    <span className="text-muted-foreground text-right text-xs">{ago(p.updated_at)}</span>
                </div>
            ))}
            {items.length > limit && (
                <div className="p-2 text-center">
                    <Button variant="ghost" size="sm" onClick={() => setLimit((l) => l + 100)}>
                        Show more
                    </Button>
                </div>
            )}
            {items.length === 0 && <NoMatches />}
        </div>
    );
}

function NoMatches() {
    return (
        <div className="text-muted-foreground col-span-full py-16 text-center text-sm">
            <SearchIcon className="mx-auto mb-3 size-5" />
            Nobody matches these filters.
        </div>
    );
}

function EmptyState() {
    return (
        <div className="flex flex-1 flex-col items-center justify-center rounded-2xl border border-dashed px-6 py-24 text-center">
            <span className="bg-gold grid size-14 place-items-center rounded-2xl text-[oklch(0.22_0.04_65)] shadow-sm">
                <UsersRoundIcon className="size-6" />
            </span>
            <h2 className="mt-5 font-serif text-3xl tracking-tight">Your guest list is empty</h2>
            <p className="text-muted-foreground mt-2 max-w-sm text-sm">Import your partner spreadsheet or add someone by hand — each person gets their invitation automatically.</p>
            <div className="mt-6 flex gap-2">
                <Button variant="gold" onClick={() => router.visit('/workspace/import')}>
                    Import a list
                </Button>
                <Button variant="outline" onClick={() => router.visit('/workspace/people?new=1')}>
                    Add one partner
                </Button>
            </div>
        </div>
    );
}
