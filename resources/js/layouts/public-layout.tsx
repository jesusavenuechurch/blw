import type { ReactNode } from 'react';
import { Head } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { useFlash } from '@/hooks/use-flash';
import { BrandWordmark } from '@/components/brand';
import { cn } from '@/lib/utils';

export default function PublicLayout({ title, children, className }: { title?: string; children: ReactNode; className?: string }) {
    useFlash();

    return (
        <div className="bg-glow relative min-h-svh">
            <Head title={title} />
            <header className="mx-auto flex max-w-6xl items-center justify-between px-4 py-5 sm:px-6">
                <a href="/" aria-label="Angel Lounges home">
                    <BrandWordmark />
                </a>
                <span className="text-muted-foreground hidden text-xs tracking-[0.18em] uppercase sm:block">Angel Court · Partner Lounge</span>
            </header>
            <main className={cn('mx-auto max-w-6xl px-4 pb-16 sm:px-6', className)}>{children}</main>
            <footer className="border-t">
                <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-6 sm:flex-row sm:px-6">
                    <div className="flex items-center gap-4 opacity-80">
                        <img src="/images/logo.svg" alt="Angel Lounges" className="h-9 w-auto" loading="lazy" />
                        <img src="/images/ror.jpg" alt="Rhapsody of Realities" className="h-8 w-auto rounded" loading="lazy" />
                        <img src="/images/ippc.jpeg" alt="IPPC" className="h-8 w-auto rounded" loading="lazy" />
                    </div>
                    <p className="text-muted-foreground text-xs">© {new Date().getFullYear()} Angel Lounges · IPPC 2025</p>
                </div>
            </footer>
            <Toaster position="top-center" />
        </div>
    );
}
