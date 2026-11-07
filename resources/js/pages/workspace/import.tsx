import { useRef, useState, type DragEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { CheckCircle2Icon, FileSpreadsheetIcon, Loader2Icon, UploadCloudIcon, XIcon } from 'lucide-react';
import WorkspaceLayout from '@/layouts/workspace-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface Props {
    result: { file: string; created: number; skipped: number } | null;
}

const COLUMNS = [
    ['title', 'Brother, Sister, Pastor…'],
    ['full_name', 'Required'],
    ['email', 'Required · existing emails are skipped'],
    ['phone', 'Optional'],
    ['kc_handle', 'Optional · KingsChat username'],
    ['region / zone / group / church', 'Optional'],
    ['tier', 'Ruby, Silver, Gold, Diamond, As One Man, Top Individual'],
    ['copies_sponsored', 'Number of ROR copies'],
];

export default function Import({ result }: Props) {
    const { data, setData, post, processing, progress, errors, reset } = useForm<{ file: File | null }>({ file: null });
    const [dragging, setDragging] = useState(false);
    const input = useRef<HTMLInputElement>(null);

    function onDrop(e: DragEvent) {
        e.preventDefault();
        setDragging(false);
        const f = e.dataTransfer.files?.[0];
        if (f) setData('file', f);
    }

    function submit() {
        post('/workspace/import', { forceFormData: true, preserveScroll: true, onSuccess: () => reset('file') });
    }

    return (
        <WorkspaceLayout title="Import">
            <div className="mx-auto grid max-w-5xl gap-6 lg:grid-cols-[1.3fr_1fr]">
                <Card>
                    <CardHeader>
                        <CardTitle className="font-serif text-3xl font-normal tracking-tight">Bring in your guest list</CardTitle>
                        <CardDescription>Upload an Excel or CSV file. Every new partner gets their registration link by email right away.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-5">
                        <div
                            onDragOver={(e) => {
                                e.preventDefault();
                                setDragging(true);
                            }}
                            onDragLeave={() => setDragging(false)}
                            onDrop={onDrop}
                            onClick={() => input.current?.click()}
                            className={cn(
                                'flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed px-6 py-14 text-center transition-all',
                                dragging ? 'border-primary bg-primary/[0.06] scale-[1.01]' : 'hover:border-foreground/25 hover:bg-accent/30',
                                errors.file && 'border-destructive/60',
                            )}
                        >
                            <input
                                ref={input}
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                className="hidden"
                                onChange={(e) => setData('file', e.target.files?.[0] ?? null)}
                            />
                            <span className="bg-gold grid size-14 place-items-center rounded-2xl text-[oklch(0.22_0.04_65)] shadow-sm">
                                <UploadCloudIcon className="size-6" />
                            </span>
                            <p className="mt-4 font-medium">Drop your spreadsheet here</p>
                            <p className="text-muted-foreground text-sm">or click to browse · .xlsx, .xls or .csv up to 10 MB</p>
                        </div>
                        {errors.file && <p className="text-destructive text-sm">{errors.file}</p>}

                        {data.file && (
                            <div className="animate-rise flex items-center gap-3 rounded-xl border p-3">
                                <FileSpreadsheetIcon className="text-success size-8" />
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-medium">{data.file.name}</p>
                                    <p className="text-muted-foreground text-xs">{(data.file.size / 1024).toFixed(0)} KB</p>
                                    {progress && (
                                        <div className="bg-muted mt-2 h-1 overflow-hidden rounded-full">
                                            <div className="bg-gold h-full transition-all" style={{ width: `${progress.percentage ?? 0}%` }} />
                                        </div>
                                    )}
                                </div>
                                <Button variant="ghost" size="icon-sm" onClick={() => setData('file', null)} aria-label="Remove file" disabled={processing}>
                                    <XIcon />
                                </Button>
                                <Button variant="gold" onClick={submit} disabled={processing}>
                                    {processing && <Loader2Icon className="animate-spin" />} Import
                                </Button>
                            </div>
                        )}

                        {result && (
                            <div className="border-success/30 bg-success/[0.06] animate-rise flex gap-3 rounded-xl border p-4">
                                <CheckCircle2Icon className="text-success mt-0.5 size-5 shrink-0" />
                                <div className="text-sm">
                                    <p className="font-medium">{result.file} imported</p>
                                    <p className="text-muted-foreground">
                                        {result.created} new partner{result.created === 1 ? '' : 's'} added and invited
                                        {result.skipped > 0 && ` · ${result.skipped} already on the list, skipped`}.
                                    </p>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card className="gap-4">
                    <CardHeader>
                        <CardTitle className="text-base">Expected columns</CardTitle>
                        <CardDescription>The first row must hold these headings.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul className="divide-y text-sm">
                            {COLUMNS.map(([c, d]) => (
                                <li key={c} className="flex flex-col gap-0.5 py-2.5">
                                    <code className="font-mono text-xs font-medium">{c}</code>
                                    <span className="text-muted-foreground text-xs">{d}</span>
                                </li>
                            ))}
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </WorkspaceLayout>
    );
}
