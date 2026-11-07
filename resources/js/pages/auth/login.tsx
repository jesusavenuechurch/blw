import type { FormEvent } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Loader2Icon } from 'lucide-react';
import { BrandWordmark } from '@/components/brand';
import { Field } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({ email: '', password: '', remember: true });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/login');
    }

    return (
        <div className="grid min-h-svh lg:grid-cols-2">
            <Head title="Sign in" />
            <div className="flex flex-col px-6 py-8 sm:px-12">
                <BrandWordmark />
                <div className="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-12">
                    <h1 className="font-serif text-4xl tracking-tight">Welcome back</h1>
                    <p className="text-muted-foreground mt-2 text-sm">Sign in to the Angel Lounges workspace.</p>
                    <form onSubmit={submit} className="mt-8 grid gap-5">
                        <Field label="Email" htmlFor="email" error={errors.email}>
                            <Input
                                id="email"
                                type="email"
                                autoComplete="username"
                                autoFocus
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                aria-invalid={!!errors.email || undefined}
                            />
                        </Field>
                        <Field label="Password" htmlFor="password" error={errors.password}>
                            <Input
                                id="password"
                                type="password"
                                autoComplete="current-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                            />
                        </Field>
                        <Label className="text-muted-foreground font-normal">
                            <Checkbox checked={data.remember} onCheckedChange={(v) => setData('remember', v === true)} /> Keep me signed in
                        </Label>
                        <Button type="submit" variant="gold" size="lg" disabled={processing}>
                            {processing && <Loader2Icon className="animate-spin" />} Sign in
                        </Button>
                    </form>
                </div>
            </div>
            <div className="relative hidden overflow-hidden bg-[oklch(0.18_0.012_60)] lg:block">
                <div className="bg-glow absolute inset-0 opacity-60" />
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_70%_80%,color-mix(in_oklch,var(--gold-2)_30%,transparent),transparent_55%)]" />
                <div className="relative flex h-full flex-col justify-end p-12 text-[oklch(0.95_0.01_85)]">
                    <p className="font-serif text-5xl leading-[1.05] tracking-tight">
                        Every partner,
                        <br />
                        <span className="text-gold italic">welcomed by name.</span>
                    </p>
                    <p className="mt-4 max-w-sm text-sm opacity-70">Invitations, registrations and lounge check-ins — all in one calm workspace.</p>
                </div>
            </div>
        </div>
    );
}
