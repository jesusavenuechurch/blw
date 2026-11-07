import { useRef, useState, type ReactNode } from 'react';
import { router } from '@inertiajs/react';
import {
    AlertTriangleIcon,
    AtSignIcon,
    CameraIcon,
    BadgeCheckIcon,
    CheckIcon,
    CopyIcon,
    DownloadIcon,
    HeartHandshakeIcon,
    Loader2Icon,
    MailIcon,
    MoreHorizontalIcon,
    PencilIcon,
    PhoneIcon,
    PlaneIcon,
    QrCodeIcon,
    RefreshCwIcon,
    ScanLineIcon,
    SendIcon,
    SofaIcon,
    Trash2Icon,
    UserPlusIcon,
} from 'lucide-react';
import { toast } from 'sonner';
import { PartnerAvatar } from '@/components/partner-avatar';
import { PartnerForm } from '@/components/people/partner-form';
import { TierBadge } from '@/components/tier';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetDescription, SheetTitle } from '@/components/ui/sheet';
import { Skeleton } from '@/components/ui/skeleton';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
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
import { EMAIL_META, STAGE_META, tierMeta } from '@/lib/tiers';
import { ago, when } from '@/lib/format';
import { cn } from '@/lib/utils';
import { shrinkImage } from '@/lib/image';
import type { FormOptions, PartnerDetail } from '@/types';

export function PartnerPanel({
    open,
    partner,
    loading,
    options,
    onClose,
}: {
    open: boolean;
    partner: PartnerDetail | null;
    loading: boolean;
    options: FormOptions;
    onClose: () => void;
}) {
    const [tab, setTab] = useState('overview');
    const [busy, setBusy] = useState<string | null>(null);
    const [confirm, setConfirm] = useState<null | 'regenerate' | 'delete'>(null);
    const photoInput = useRef<HTMLInputElement>(null);

    async function uploadPhoto(file?: File) {
        if (!partner || !file) return;
        setBusy('photo');
        const photo = await shrinkImage(file);
        router.post(`/workspace/people/${partner.id}/photo`, { photo }, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                setBusy(null);
                if (photoInput.current) photoInput.current.value = '';
            },
        });
    }

    function removePhoto() {
        if (!partner) return;
        setBusy('photo');
        router.delete(`/workspace/people/${partner.id}/photo`, { preserveScroll: true, preserveState: true, onFinish: () => setBusy(null) });
    }

    function act(action: 'resend' | 'regenerate' | 'qr') {
        if (!partner) return;
        setBusy(action);
        router.post(`/workspace/people/${partner.id}/${action}`, {}, { preserveScroll: true, preserveState: true, onFinish: () => setBusy(null) });
    }

    function destroy() {
        if (!partner) return;
        router.delete(`/workspace/people/${partner.id}`, { preserveScroll: true, onSuccess: onClose });
    }

    function copyLink() {
        if (!partner?.registration_url) return;
        navigator.clipboard?.writeText(partner.registration_url).then(
            () => toast.success('Registration link copied'),
            () => toast.error('Couldn’t copy — select the link and copy it manually.'),
        );
    }

    const color = tierMeta(partner?.tier).color;

    return (
        <Sheet open={open} onOpenChange={(o) => !o && onClose()}>
            <SheetContent className="w-full gap-0 p-0 sm:max-w-[560px]">
                {!partner || (loading && !partner) ? (
                    <div className="space-y-4 p-6">
                        <SheetTitle className="sr-only">Loading partner</SheetTitle>
                        <Skeleton className="size-16 rounded-full" />
                        <Skeleton className="h-8 w-2/3" />
                        <Skeleton className="h-4 w-1/2" />
                        <Skeleton className="h-40 w-full" />
                    </div>
                ) : (
                    <Tabs value={tab} onValueChange={setTab} className="flex h-full min-h-0 flex-col gap-0">
                        {/* Header */}
                        <div
                            className="relative border-b px-6 pt-8 pb-4"
                            style={{ backgroundImage: `radial-gradient(30rem 12rem at 0% -40%, color-mix(in oklch, ${color} 22%, transparent), transparent 70%)` }}
                        >
                            <div className="flex items-start gap-4">
                                <PartnerAvatar name={partner.full_name} tier={partner.tier} photo={partner.photo_url} className="size-14 text-lg" />
                                <div className="min-w-0 flex-1 pr-8">
                                    <SheetTitle className="font-serif text-[1.75rem] leading-tight font-normal tracking-tight">
                                        {partner.title} {partner.full_name}
                                    </SheetTitle>
                                    <SheetDescription className="mt-0.5 truncate">{[partner.designation, partner.church].filter(Boolean).join(' · ') || 'No designation yet'}</SheetDescription>
                                    <div className="mt-3 flex flex-wrap items-center gap-1.5">
                                        <TierBadge tier={partner.tier} className="bg-card" />
                                        <StageBadge stage={partner.stage} />
                                        <Badge variant={EMAIL_META[partner.email_state].variant}>{EMAIL_META[partner.email_state].label}</Badge>
                                    </div>
                                </div>
                            </div>
                            <div className="mt-5 flex flex-wrap items-center gap-2">
                                {!partner.is_registered && (
                                    <Button size="sm" variant="gold" onClick={() => act('resend')} disabled={busy !== null}>
                                        {busy === 'resend' ? <Loader2Icon className="animate-spin" /> : <SendIcon />} Resend invitation
                                    </Button>
                                )}
                                {partner.is_registered && (
                                    <Button size="sm" variant="outline" onClick={() => act('resend')} disabled={busy !== null}>
                                        {busy === 'resend' ? <Loader2Icon className="animate-spin" /> : <SendIcon />} Resend email
                                    </Button>
                                )}
                                {partner.registration_url && !partner.is_registered && (
                                    <Button size="sm" variant="outline" onClick={copyLink}>
                                        <CopyIcon /> Copy link
                                    </Button>
                                )}
                                <Button size="sm" variant="outline" onClick={() => setTab('edit')}>
                                    <PencilIcon /> Edit
                                </Button>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button size="icon-sm" variant="ghost" aria-label="More actions">
                                            <MoreHorizontalIcon />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" className="w-56">
                                        <DropdownMenuItem onSelect={() => act('qr')}>
                                            <QrCodeIcon /> {partner.has_qr ? 'Regenerate QR code' : 'Generate QR code'}
                                        </DropdownMenuItem>
                                        {!partner.is_registered && (
                                            <DropdownMenuItem onSelect={() => setConfirm('regenerate')}>
                                                <RefreshCwIcon /> New registration link
                                            </DropdownMenuItem>
                                        )}
                                        {partner.email && (
                                            <DropdownMenuItem asChild>
                                                <a href={`mailto:${partner.email}`}>
                                                    <MailIcon /> Write an email
                                                </a>
                                            </DropdownMenuItem>
                                        )}
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem variant="destructive" onSelect={() => setConfirm('delete')}>
                                            <Trash2Icon /> Remove partner
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            <TabsList className="mt-5 -mb-px h-auto w-full justify-start gap-4 rounded-none bg-transparent p-0">
                                {[
                                    ['overview', 'Overview'],
                                    ['journey', 'Journey'],
                                    ['edit', 'Edit details'],
                                ].map(([v, l]) => (
                                    <TabsTrigger
                                        key={v}
                                        value={v}
                                        className="data-[state=active]:border-b-primary text-muted-foreground data-[state=active]:text-foreground flex-none rounded-none border-0 border-b-2 border-transparent px-0 pb-2.5 data-[state=active]:bg-transparent data-[state=active]:shadow-none dark:data-[state=active]:bg-transparent"
                                    >
                                        {l}
                                    </TabsTrigger>
                                ))}
                            </TabsList>
                        </div>

                        <TabsContent value="overview" className="scrollbar-thin min-h-0 overflow-y-auto px-6 py-6">
                            {partner.email_state === 'failed' && (
                                <div className="border-destructive/30 bg-destructive/5 mb-6 flex gap-3 rounded-xl border p-4 text-sm">
                                    <AlertTriangleIcon className="text-destructive mt-0.5 size-4 shrink-0" />
                                    <div className="min-w-0">
                                        <p className="font-medium">The last email didn’t go through</p>
                                        <p className="text-muted-foreground mt-1 break-words">{partner.email_response}</p>
                                    </div>
                                </div>
                            )}

                            <div className="grid gap-6 sm:grid-cols-[1fr_170px]">
                                <dl className="space-y-4 text-sm">
                                    <Row icon={<MailIcon />} label="Email">
                                        {partner.email ?? '—'}
                                    </Row>
                                    <Row icon={<PhoneIcon />} label="Phone">
                                        {partner.phone ?? '—'}
                                    </Row>
                                    <Row icon={<AtSignIcon />} label="KingsChat">
                                        {partner.kc_handle ? `@${partner.kc_handle}` : '—'}
                                    </Row>
                                    <Row icon={<PlaneIcon />} label="IPPC 2025">
                                        {partner.will_attend_ippc ? 'Attending' : 'Not attending'}
                                    </Row>
                                    <Row icon={<SofaIcon />} label="Angel Lounge">
                                        {partner.will_be_at_exhibition ? 'Honoured guest' : 'Not visiting'}
                                    </Row>
                                    <Row icon={<HeartHandshakeIcon />} label="Spouse">
                                        {partner.coming_with_spouse ? (
                                            <>
                                                {partner.spouse_title} {partner.spouse_name}
                                                {partner.spouse_kc_handle && <span className="text-muted-foreground"> · @{partner.spouse_kc_handle}</span>}
                                            </>
                                        ) : (
                                            'Coming alone'
                                        )}
                                    </Row>
                                </dl>
                                <div className="flex flex-col items-center">
                                    {partner.qr_code_url ? (
                                        <>
                                            <div className="rounded-xl bg-white p-2 shadow-sm ring-1 ring-black/5">
                                                <img src={partner.qr_code_url} alt="QR pass" className="size-36 object-contain" />
                                            </div>
                                            <a href={partner.qr_code_url} download={`QR_${partner.full_name}.png`} className="text-muted-foreground hover:text-foreground mt-2 flex items-center gap-1 text-xs">
                                                <DownloadIcon className="size-3" /> Download pass
                                            </a>
                                        </>
                                    ) : (
                                        <button
                                            onClick={() => act('qr')}
                                            className="text-muted-foreground hover:text-foreground hover:border-foreground/20 grid size-40 place-items-center rounded-xl border border-dashed text-xs transition-colors"
                                        >
                                            <span className="flex flex-col items-center gap-2">
                                                <QrCodeIcon className="size-6" />
                                                Generate QR pass
                                            </span>
                                        </button>
                                    )}
                                </div>
                            </div>

                            <div className="mt-8 grid grid-cols-3 gap-3">
                                <Stat label="ROR copies" value={partner.ror_copies_sponsored.toLocaleString()} />
                                <Stat label="Check-ins" value={String(partner.check_ins.length)} />
                                <Stat label="Added" value={ago(partner.created_at)} small />
                            </div>

                            <section className="mt-8">
                                <h4 className="text-muted-foreground mb-3 text-[11px] font-semibold tracking-wider uppercase">Photo for the scanner</h4>
                                <div className="flex items-center gap-4 rounded-xl border p-4">
                                    <div className="bg-muted grid size-20 shrink-0 place-items-center overflow-hidden rounded-full">
                                        {partner.photo_url ? (
                                            <img src={partner.photo_url} alt={`Photo of ${partner.full_name}`} className="size-full object-cover" />
                                        ) : (
                                            <CameraIcon className="text-muted-foreground size-6" />
                                        )}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-medium">{partner.photo_url ? 'Shown when their pass is scanned' : 'No photo yet'}</p>
                                        <p className="text-muted-foreground text-xs">
                                            {partner.photo_consent_at ? `Consent given ${when(partner.photo_consent_at)}` : 'Ushers see this face to confirm it’s really them.'}
                                        </p>
                                        <div className="mt-2.5 flex gap-2">
                                            <Button size="sm" variant="outline" onClick={() => photoInput.current?.click()} disabled={busy === 'photo'}>
                                                {busy === 'photo' ? <Loader2Icon className="animate-spin" /> : <CameraIcon />} {partner.photo_url ? 'Replace' : 'Upload photo'}
                                            </Button>
                                            {partner.photo_url && (
                                                <Button size="sm" variant="ghost" onClick={removePhoto} disabled={busy === 'photo'}>
                                                    <Trash2Icon /> Remove
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                    <input ref={photoInput} type="file" accept="image/*" className="hidden" onChange={(e) => uploadPhoto(e.target.files?.[0])} />
                                </div>
                            </section>

                            <section className="mt-8">
                                <h4 className="text-muted-foreground mb-3 text-[11px] font-semibold tracking-wider uppercase">Church</h4>
                                <div className="bg-muted/40 grid grid-cols-2 gap-x-6 gap-y-3 rounded-xl border p-4 text-sm">
                                    {(
                                        [
                                            ['Region', partner.region],
                                            ['Zone', partner.zone],
                                            ['Group', partner.group],
                                            ['Church', partner.church],
                                        ] as const
                                    ).map(([l, v]) => (
                                        <div key={l}>
                                            <p className="text-muted-foreground text-xs">{l}</p>
                                            <p className="truncate">{v || '—'}</p>
                                        </div>
                                    ))}
                                </div>
                            </section>

                            {partner.delivery_method && (
                                <section className="mt-8">
                                    <h4 className="text-muted-foreground mb-3 text-[11px] font-semibold tracking-wider uppercase">ROR gift delivery</h4>
                                    <p className="bg-muted/40 rounded-xl border p-4 text-sm whitespace-pre-line">{partner.delivery_method}</p>
                                </section>
                            )}

                            {partner.registration_url && !partner.is_registered && (
                                <section className="mt-8">
                                    <h4 className="text-muted-foreground mb-3 text-[11px] font-semibold tracking-wider uppercase">Registration link</h4>
                                    <div className="flex items-center gap-2 rounded-xl border p-1.5 pl-3">
                                        <code className="text-muted-foreground min-w-0 flex-1 truncate font-mono text-xs">{partner.registration_url}</code>
                                        <Button size="sm" variant="secondary" onClick={copyLink}>
                                            <CopyIcon /> Copy
                                        </Button>
                                    </div>
                                </section>
                            )}
                        </TabsContent>

                        <TabsContent value="journey" className="scrollbar-thin min-h-0 overflow-y-auto px-6 py-6">
                            <ol className="relative">
                                {partner.timeline.map((e, i) => (
                                    <li key={i} className="relative flex gap-4 pb-7 last:pb-0">
                                        {i < partner.timeline.length - 1 && <span className="bg-border absolute top-8 bottom-0 left-[15px] w-px" />}
                                        <span
                                            className={cn(
                                                'grid size-8 shrink-0 place-items-center rounded-full border bg-card',
                                                e.type === 'checked_in' && 'bg-success/10 border-success/30 text-success',
                                                e.type === 'registered' && 'bg-primary/10 border-primary/30 text-primary',
                                                e.type === 'email_failed' && 'bg-destructive/10 border-destructive/30 text-destructive',
                                            )}
                                        >
                                            <TimelineIcon type={e.type} />
                                        </span>
                                        <div className="pt-1">
                                            <p className="text-sm font-medium">{e.label}</p>
                                            {e.at && <p className="text-muted-foreground text-xs">{when(e.at)}</p>}
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        </TabsContent>

                        <TabsContent value="edit" className="flex min-h-0 flex-col pt-6">
                            <PartnerForm key={partner.id + (partner.updated_at ?? '')} partner={partner} options={options} onDone={() => setTab('overview')} onCancel={() => setTab('overview')} />
                        </TabsContent>
                    </Tabs>
                )}

                <AlertDialog open={confirm !== null} onOpenChange={(o) => !o && setConfirm(null)}>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>{confirm === 'delete' ? `Remove ${partner?.full_name}?` : 'Send a new registration link?'}</AlertDialogTitle>
                            <AlertDialogDescription>
                                {confirm === 'delete'
                                    ? 'Their details, QR pass and check-in history will be deleted. This can’t be undone.'
                                    : `This generates a new link for ${partner?.full_name} and emails it to them. The old link will stop working.`}
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction
                                variant={confirm === 'delete' ? 'destructive' : 'gold'}
                                onClick={() => (confirm === 'delete' ? destroy() : act('regenerate'))}
                            >
                                {confirm === 'delete' ? 'Remove' : 'Regenerate & send'}
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </SheetContent>
        </Sheet>
    );
}

export function StageBadge({ stage, className }: { stage: PartnerDetail['stage']; className?: string }) {
    const meta = STAGE_META[stage];
    return (
        <Tooltip>
            <TooltipTrigger asChild>
                <span className={cn('bg-card inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-xs font-medium', className)}>
                    <span className="size-1.5 rounded-full" style={{ background: meta.color }} />
                    {meta.label}
                </span>
            </TooltipTrigger>
            <TooltipContent>{meta.hint}</TooltipContent>
        </Tooltip>
    );
}

function Row({ icon, label, children }: { icon: ReactNode; label: string; children: ReactNode }) {
    return (
        <div className="flex gap-3">
            <span className="text-muted-foreground mt-0.5 [&_svg]:size-4">{icon}</span>
            <div className="min-w-0">
                <dt className="text-muted-foreground text-xs">{label}</dt>
                <dd className="break-words">{children}</dd>
            </div>
        </div>
    );
}

function Stat({ label, value, small }: { label: string; value: string; small?: boolean }) {
    return (
        <div className="rounded-xl border p-3">
            <p className={cn('font-semibold tracking-tight tabular-nums', small ? 'text-sm leading-7' : 'text-xl')}>{value}</p>
            <p className="text-muted-foreground text-xs">{label}</p>
        </div>
    );
}

function TimelineIcon({ type }: { type: string }) {
    const cls = 'size-4';
    switch (type) {
        case 'email':
            return <MailIcon className={cls} />;
        case 'email_failed':
            return <AlertTriangleIcon className={cls} />;
        case 'registered':
            return <BadgeCheckIcon className={cls} />;
        case 'checked_in':
            return <ScanLineIcon className={cls} />;
        case 'created':
            return <UserPlusIcon className={cls} />;
        default:
            return <CheckIcon className={cls} />;
    }
}
