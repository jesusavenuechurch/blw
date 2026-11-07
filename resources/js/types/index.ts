export type Tier = 'ruby' | 'silver' | 'gold' | 'diamond' | 'as_one_man' | 'top_individual';
export type Stage = 'invited' | 'delivered' | 'registered' | 'checked_in';
export type EmailState = 'sent' | 'pending' | 'failed' | 'none';

export interface SharedProps {
    app: { name: string; event: string };
    auth: { user: { id: number; name: string; email: string } | null };
    flash: { success?: string | null; error?: string | null };
    errors: Record<string, string>;
    [key: string]: unknown;
}

export interface PartnerCard {
    id: number;
    title: string | null;
    full_name: string;
    email: string | null;
    phone: string | null;
    kc_handle: string | null;
    designation: string | null;
    region: string | null;
    zone: string | null;
    church: string | null;
    tier: Tier | string;
    stage: Stage;
    email_state: EmailState;
    is_registered: boolean;
    will_attend_ippc: boolean;
    will_be_at_exhibition: boolean;
    coming_with_spouse: boolean;
    spouse_name: string | null;
    ror_copies_sponsored: number;
    has_qr: boolean;
    photo_url: string | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface PartnerDetail extends PartnerCard {
    spouse_title: string | null;
    spouse_kc_handle: string | null;
    group: string | null;
    delivery_method: string | null;
    email_response: string | null;
    registration_url: string | null;
    qr_code_url: string | null;
    photo_consent_at: string | null;
    token_used_at: string | null;
    check_ins: { id: number; checked_in_at: string }[];
    timeline: { type: string; label: string; at: string | null }[];
}

export interface FormOptions {
    titles: string[];
    designations: string[];
    tiers: Tier[];
}
