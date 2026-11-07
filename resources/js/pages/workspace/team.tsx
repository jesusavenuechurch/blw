import { useState, type FormEvent } from 'react';
import { router, useForm, usePage } from '@inertiajs/react';
import { Loader2Icon, MoreHorizontalIcon, PencilIcon, Trash2Icon, UserPlusIcon } from 'lucide-react';
import WorkspaceLayout from '@/layouts/workspace-layout';
import { Field } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { day } from '@/lib/format';
import { initials } from '@/lib/utils';
import type { SharedProps } from '@/types';

interface Member {
    id: number;
    name: string;
    email: string;
    created_at: string | null;
}

export default function Team({ members }: { members: Member[] }) {
    const { auth } = usePage<SharedProps>().props;
    const [editing, setEditing] = useState<Member | 'new' | null>(null);

    return (
        <WorkspaceLayout
            title="Team"
            actions={
                <Button size="sm" variant="outline" onClick={() => setEditing('new')}>
                    <UserPlusIcon /> Invite
                </Button>
            }
        >
            <div className="mx-auto max-w-3xl">
                <h2 className="font-serif text-3xl tracking-tight">Your team</h2>
                <p className="text-muted-foreground mt-1 text-sm">Everyone here can sign in to the workspace and manage the guest list.</p>
                <Card className="mt-6 gap-0 py-0">
                    {members.map((m) => (
                        <div key={m.id} className="flex items-center gap-4 border-b px-5 py-4 last:border-0">
                            <span className="bg-gold grid size-10 place-items-center rounded-full text-sm font-semibold text-[oklch(0.22_0.04_65)]">{initials(m.name)}</span>
                            <div className="min-w-0 flex-1">
                                <p className="truncate font-medium">
                                    {m.name} {m.id === auth.user?.id && <span className="text-muted-foreground text-xs font-normal">(you)</span>}
                                </p>
                                <p className="text-muted-foreground truncate text-sm">{m.email}</p>
                            </div>
                            <span className="text-muted-foreground hidden text-xs sm:block">Joined {day(m.created_at)}</span>
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="ghost" size="icon-sm" aria-label={`Actions for ${m.name}`}>
                                        <MoreHorizontalIcon />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem onSelect={() => setEditing(m)}>
                                        <PencilIcon /> Edit
                                    </DropdownMenuItem>
                                    {m.id !== auth.user?.id && (
                                        <DropdownMenuItem
                                            variant="destructive"
                                            onSelect={() => {
                                                if (confirm(`Remove ${m.name} from the team?`)) router.delete(`/workspace/team/${m.id}`, { preserveScroll: true });
                                            }}
                                        >
                                            <Trash2Icon /> Remove
                                        </DropdownMenuItem>
                                    )}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    ))}
                </Card>
            </div>
            <MemberDialog key={editing === 'new' ? 'new' : (editing?.id ?? 'none')} member={editing} onClose={() => setEditing(null)} />
        </WorkspaceLayout>
    );
}

function MemberDialog({ member, onClose }: { member: Member | 'new' | null; onClose: () => void }) {
    const isNew = member === 'new';
    const existing = member && member !== 'new' ? member : null;
    const { data, setData, post, put, processing, errors } = useForm({
        name: existing?.name ?? '',
        email: existing?.email ?? '',
        password: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        const opts = { preserveScroll: true, onSuccess: onClose };
        if (existing) put(`/workspace/team/${existing.id}`, opts);
        else post('/workspace/team', opts);
    }

    return (
        <Dialog open={member !== null} onOpenChange={(o) => !o && onClose()}>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-5">
                    <DialogHeader>
                        <DialogTitle>{isNew ? 'Add a team member' : `Edit ${existing?.name}`}</DialogTitle>
                        <DialogDescription>{isNew ? 'They’ll sign in with this email and password.' : 'Leave the password blank to keep it unchanged.'}</DialogDescription>
                    </DialogHeader>
                    <Field label="Name" required error={errors.name}>
                        <Input value={data.name} onChange={(e) => setData('name', e.target.value)} autoFocus />
                    </Field>
                    <Field label="Email" required error={errors.email}>
                        <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                    </Field>
                    <Field label="Password" required={isNew} error={errors.password} hint="At least 8 characters.">
                        <Input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} autoComplete="new-password" />
                    </Field>
                    <DialogFooter>
                        <Button type="button" variant="ghost" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" variant="gold" disabled={processing}>
                            {processing && <Loader2Icon className="animate-spin" />} {isNew ? 'Add member' : 'Save'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
