import { LinkIcon, MailIcon } from 'lucide-react';
import PublicLayout from '@/layouts/public-layout';

export default function Invalid({ message, name }: { message: string; name?: string }) {
    return (
        <PublicLayout title="Link unavailable">
            <div className="animate-rise mx-auto flex max-w-lg flex-col items-center pt-16 pb-10 text-center">
                <span className="bg-card grid size-16 place-items-center rounded-2xl border shadow-sm">
                    <LinkIcon className="text-primary size-7" />
                </span>
                <h1 className="mt-6 font-serif text-4xl tracking-tight">{name ? `Hello, ${name.split(' ')[0]}` : "This link isn't available"}</h1>
                <p className="text-muted-foreground mt-3 text-balance">{message}</p>
                <div className="bg-card mt-8 flex items-center gap-3 rounded-xl border px-4 py-3 text-left text-sm shadow-xs">
                    <MailIcon className="text-muted-foreground size-4 shrink-0" />
                    <span className="text-muted-foreground">
                        Need help? Reply to your invitation email and the Angel Lounges team will sort it out.
                    </span>
                </div>
            </div>
        </PublicLayout>
    );
}
