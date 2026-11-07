import { useEffect, useRef, useState } from 'react';
import { CameraIcon, Loader2Icon, Trash2Icon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { shrinkImage } from '@/lib/image';
import { cn, initials } from '@/lib/utils';

/** Round photo preview with pick / replace / remove. Emits a shrunk JPEG File. */
export function PhotoPicker({
    value,
    existingUrl,
    name,
    onChange,
    invalid,
    className,
}: {
    value: File | null;
    existingUrl?: string | null;
    name: string;
    onChange: (file: File | null) => void;
    invalid?: boolean;
    className?: string;
}) {
    const input = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (!value) {
            setPreview(null);
            return;
        }
        const url = URL.createObjectURL(value);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [value]);

    async function pick(file?: File) {
        if (!file) return;
        setBusy(true);
        try {
            onChange(await shrinkImage(file));
        } finally {
            setBusy(false);
            if (input.current) input.current.value = '';
        }
    }

    const shown = preview ?? existingUrl ?? null;

    return (
        <div className={cn('flex items-center gap-5', className)}>
            <button
                type="button"
                onClick={() => input.current?.click()}
                className={cn(
                    'group bg-muted relative grid size-24 shrink-0 place-items-center overflow-hidden rounded-full border-2 border-dashed transition-all',
                    shown ? 'border-transparent' : 'hover:border-primary/60',
                    invalid && 'border-destructive',
                )}
                aria-label={shown ? 'Change photo' : 'Add a photo'}
            >
                {shown ? (
                    <img src={shown} alt="" className="size-full object-cover" />
                ) : (
                    <span className="text-muted-foreground font-serif text-3xl">{initials(name)}</span>
                )}
                <span className="absolute inset-0 grid place-items-center bg-black/40 text-white opacity-0 transition-opacity group-hover:opacity-100">
                    {busy ? <Loader2Icon className="size-5 animate-spin" /> : <CameraIcon className="size-5" />}
                </span>
            </button>
            <div className="min-w-0 space-y-2">
                <div className="flex flex-wrap gap-2">
                    <Button type="button" size="sm" variant="outline" onClick={() => input.current?.click()} disabled={busy}>
                        {busy ? <Loader2Icon className="animate-spin" /> : <CameraIcon />} {shown ? 'Change photo' : 'Add a photo'}
                    </Button>
                    {value && (
                        <Button type="button" size="sm" variant="ghost" onClick={() => onChange(null)}>
                            <Trash2Icon /> Remove
                        </Button>
                    )}
                </div>
                <p className="text-muted-foreground text-xs">A clear photo of your face, like a passport picture. JPG or PNG.</p>
            </div>
            <input ref={input} type="file" accept="image/*" className="hidden" onChange={(e) => pick(e.target.files?.[0])} />
        </div>
    );
}
