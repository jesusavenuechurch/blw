<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Support\Options;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class PartnerRegistrationController extends Controller
{
    /**
     * Show the registration form
     */
    public function show($token)
    {
        // Find partner by registration token
        $partner = Partner::where('registration_token', $token)->first();

        // ✅ Check only that the token exists and partner not yet registered
        if (!$partner) {
            return Inertia::render('partner/invalid', [
                'message' => 'Invalid registration link. Please contact support.',
            ]);
        }

        if ($partner->is_registered) {
            return Inertia::render('partner/invalid', [
                'message' => 'This registration link has already been used on ' .
                            optional($partner->token_used_at)->format('F j, Y \a\t g:i A'),
                'name' => $partner->full_name,
            ]);
        }

        // ✅ Do not mark the token as used here — just show the form
        return Inertia::render('partner/register', [
            'token' => $token,
            'partner' => [
                'title' => $partner->title,
                'designation' => $partner->designation,
                'full_name' => $partner->full_name,
                'email' => $partner->email,
                'phone' => $partner->phone,
                'region' => $partner->region,
                'zone' => $partner->zone,
                'group' => $partner->group,
                'church' => $partner->church,
                'tier' => $partner->tier,
                'ror_copies_sponsored' => $partner->ror_copies_sponsored,
                'will_attend_ippc' => (bool) $partner->will_attend_ippc,
                'will_be_at_exhibition' => (bool) $partner->will_be_at_exhibition,
                'delivery_method' => $partner->delivery_method,
                'coming_with_spouse' => (bool) $partner->coming_with_spouse,
                'spouse_title' => $partner->spouse_title,
                'spouse_name' => $partner->spouse_name,
                'spouse_kc_handle' => $partner->spouse_kc_handle,
            ],
            'options' => [
                'titles' => Options::TITLES,
                'designations' => Options::DESIGNATIONS,
            ],
        ]);
    }
    /**
     * Process the registration form
     */
    public function store(Request $request, $token)
    {
        $partner = Partner::where('registration_token', $token)->first();

        // ✅ Check that token exists and not yet registered
        if (!$partner || $partner->is_registered) {
            return redirect()->route('partner.register', $token)
                ->with('error', 'Invalid or expired registration link.');
        }

        // Validate form data
        $validator = Validator::make($request->all(), [
            'title' => ['required', Rule::in(Options::TITLES)],
            'designation' => ['required', Rule::in(Options::DESIGNATIONS)],
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'kc_handle' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'zone' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:255',
            'church' => 'nullable|string|max:255',
            'ror_copies_sponsored' => 'nullable|integer|min:0',
            'will_attend_ippc' => 'boolean',
            'will_be_at_exhibition' => 'nullable|boolean',
            'coming_with_spouse' => 'boolean',
            'spouse_title' => ['nullable', 'required_if:coming_with_spouse,true', Rule::in(Options::TITLES)],
            'spouse_name' => 'nullable|required_if:coming_with_spouse,true|string|max:255',
            'delivery_method' => [
                ($request->boolean('will_attend_ippc') && $request->boolean('will_be_at_exhibition')) ? 'nullable' : 'required',
                'nullable', 'string', 'max:1000',
            ],
            'spouse_kc_handle' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Prepare update data
            $updateData = [
                'title' => $request->title,
                'designation' => $request->designation,
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'kc_handle' => $request->kc_handle,
                'region' => $request->region,
                'zone' => $request->zone,
                'group' => $request->group,
                'church' => $request->church,
                'ror_copies_sponsored' => $request->ror_copies_sponsored ?? 0,
                'will_attend_ippc' => $request->boolean('will_attend_ippc'),
                'will_be_at_exhibition' => $request->will_attend_ippc ? $request->boolean('will_be_at_exhibition') : false,
                'delivery_method' => !$request->will_attend_ippc ? $request->delivery_method : null,
                'coming_with_spouse' => $request->boolean('coming_with_spouse'),
            ];

            if ($request->boolean('coming_with_spouse')) {
                $updateData['spouse_title'] = $request->spouse_title;
                $updateData['spouse_name'] = $request->spouse_name;
                $updateData['spouse_kc_handle'] = $request->spouse_kc_handle;
            } else {
                $updateData['spouse_title'] = null;
                $updateData['spouse_name'] = null;
                $updateData['spouse_kc_handle'] = null;
            }

            // ✅ Update partner information
            $partner->update($updateData);

            // ✅ Now mark token as used AFTER successful save
            $partner->markTokenAsUsed();

            Log::info("Partner registration completed for: {$partner->email}");

            // Remember who just registered so the success page shows *their* QR code.
            $request->session()->put('registered_partner_id', $partner->id);

            return redirect()->route('partner.success');

        } catch (\Exception $e) {
            Log::error("Registration failed for token {$token}: " . $e->getMessage());
            
            return back()
                ->with('error', 'Registration failed. Please try again or contact support.')
                ->withInput();
        }
    }

    public function success(Request $request)
    {
        $partner = Partner::find($request->session()->get('registered_partner_id'));

        if (!$partner || !$partner->is_registered) {
            return Inertia::render('partner/invalid', [
                'message' => 'We couldn’t find your registration in this browser. Please use the link in your email.',
            ]);
        }

        return Inertia::render('partner/success', [
            'partner' => [
                'title' => $partner->title,
                'full_name' => $partner->full_name,
                'tier' => $partner->tier,
                'ror_copies_sponsored' => (int) $partner->ror_copies_sponsored,
                'qr_code_url' => $partner->qr_code_path ? asset('storage/' . $partner->qr_code_path) : null,
                'registered_at' => $partner->token_used_at?->toIso8601String(),
                'will_attend_ippc' => (bool) $partner->will_attend_ippc,
                'will_be_at_exhibition' => (bool) $partner->will_be_at_exhibition,
                'coming_with_spouse' => (bool) $partner->coming_with_spouse,
                'spouse_name' => $partner->spouse_name,
            ],
        ]);
    }

        /**
     * Verify partner via QR code scan
     */
    public function verify($id)
    {
        try {
            $partner = Partner::findOrFail($id);

            return Inertia::render('partner/verify', [
                'partner' => [
                    'title' => $partner->title,
                    'full_name' => $partner->full_name,
                    'tier' => $partner->tier,
                    'is_registered' => (bool) $partner->is_registered,
                    'coming_with_spouse' => (bool) $partner->coming_with_spouse,
                    'spouse_title' => $partner->spouse_title,
                    'spouse_name' => $partner->spouse_name,
                ],
            ]);

        } catch (\Exception $e) {
            return Inertia::render('partner/invalid', [
                'message' => 'Invalid verification code. Please contact support.',
            ]);
        }
    }
}