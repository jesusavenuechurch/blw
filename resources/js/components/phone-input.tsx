import { useMemo, useState } from 'react';
import { ChevronsUpDownIcon } from 'lucide-react';
import { COUNTRY_CODES } from '@/lib/country-codes';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

/** Splits a stored "+234801…" number into code + local part. */
export function splitPhone(phone?: string | null): { code: string; number: string } {
    if (phone && phone.startsWith('+')) {
        const match = [...COUNTRY_CODES].sort((a, b) => b.code.length - a.code.length).find((c) => phone.startsWith(c.code));
        if (match) return { code: match.code, number: phone.slice(match.code.length) };
    }
    return { code: '+234', number: phone ?? '' };
}

export function joinPhone(code: string, number: string) {
    const n = number.trim();
    if (!n) return '';
    return n.startsWith('+') ? n : code + n;
}

export function PhoneInput({
    code,
    number,
    onCodeChange,
    onNumberChange,
    invalid,
    id,
}: {
    code: string;
    number: string;
    onCodeChange: (c: string) => void;
    onNumberChange: (n: string) => void;
    invalid?: boolean;
    id?: string;
}) {
    const [open, setOpen] = useState(false);
    const selected = useMemo(() => COUNTRY_CODES.find((c) => c.code === code), [code]);

    return (
        <div className="flex gap-2">
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <button
                        type="button"
                        className="border-input dark:bg-input/30 hover:bg-accent/50 focus-visible:ring-ring/40 flex h-10 shrink-0 items-center gap-1.5 rounded-md border bg-background px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                        aria-label="Country code"
                    >
                        <span className="text-base leading-none">{selected?.flag ?? '🌐'}</span>
                        <span className="tabular-nums">{code}</span>
                        <ChevronsUpDownIcon className="text-muted-foreground size-3.5" />
                    </button>
                </PopoverTrigger>
                <PopoverContent className="w-72 p-0" align="start">
                    <Command>
                        <CommandInput placeholder="Search country or code…" />
                        <CommandList className="max-h-64">
                            <CommandEmpty>No country found.</CommandEmpty>
                            <CommandGroup>
                                {COUNTRY_CODES.map((c) => (
                                    <CommandItem
                                        key={c.name + c.code}
                                        value={`${c.name} ${c.code}`}
                                        onSelect={() => {
                                            onCodeChange(c.code);
                                            setOpen(false);
                                        }}
                                    >
                                        <span className="text-base">{c.flag}</span>
                                        <span className="flex-1 truncate">{c.name}</span>
                                        <span className="text-muted-foreground tabular-nums">{c.code}</span>
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
            <Input
                id={id}
                type="tel"
                inputMode="tel"
                value={number}
                onChange={(e) => onNumberChange(e.target.value)}
                placeholder="801 234 5678"
                aria-invalid={invalid || undefined}
                className={cn('tabular-nums')}
            />
        </div>
    );
}
