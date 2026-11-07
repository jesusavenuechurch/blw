<?php

namespace App\Http\Requests;

use App\Models\Partner;
use App\Support\Options;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/editing a partner in the workspace.
 * Mirrors the rules of the old Filament PartnerResource form.
 */
class PartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $partner = $this->route('partner');
        $needsDelivery = ! ($this->boolean('will_attend_ippc') && $this->boolean('will_be_at_exhibition'));

        return [
            'title' => ['required', Rule::in(Options::TITLES)],
            'designation' => ['required', Rule::in(Options::DESIGNATIONS)],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('partners', 'email')->ignore($partner?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'region' => ['nullable', 'string', 'max:255'],
            'zone' => ['nullable', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:255'],
            'church' => ['nullable', 'string', 'max:255'],
            'tier' => ['required', Rule::in(Partner::TIERS)],
            'ror_copies_sponsored' => ['nullable', 'integer', 'min:0'],
            'will_attend_ippc' => ['boolean'],
            'will_be_at_exhibition' => ['boolean'],
            'delivery_method' => [$needsDelivery ? 'required' : 'nullable', 'nullable', 'string', 'max:1000'],
            'coming_with_spouse' => ['boolean'],
            'spouse_title' => ['nullable', 'required_if:coming_with_spouse,true', Rule::in(Options::TITLES)],
            'spouse_name' => ['nullable', 'required_if:coming_with_spouse,true', 'string', 'max:255'],
            'spouse_kc_handle' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_method.required' => 'Tell us how the ROR gifts should be delivered.',
            'spouse_title.required_if' => 'Pick a title for the spouse.',
            'spouse_name.required_if' => 'Enter the spouse’s name.',
        ];
    }

    /** Validated data with the same field-clearing rules the old form applied. */
    public function partnerData(): array
    {
        $data = $this->validated();

        $data['will_attend_ippc'] = $this->boolean('will_attend_ippc');
        $data['will_be_at_exhibition'] = $data['will_attend_ippc'] && $this->boolean('will_be_at_exhibition');
        $data['coming_with_spouse'] = $this->boolean('coming_with_spouse');
        $data['ror_copies_sponsored'] = (int) ($data['ror_copies_sponsored'] ?? 0);

        if ($data['will_attend_ippc'] && $data['will_be_at_exhibition']) {
            $data['delivery_method'] = null;
        }
        if (! $data['coming_with_spouse']) {
            $data['spouse_title'] = $data['spouse_name'] = $data['spouse_kc_handle'] = null;
        }

        return $data;
    }
}
