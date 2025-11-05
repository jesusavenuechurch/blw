<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
            return view('partner.registration-invalid', [
                'message' => 'Invalid registration link. Please contact support.',
                'partner' => null
            ]);
        }

        if ($partner->is_registered) {
            return view('partner.registration-invalid', [
                'message' => 'This registration link has already been used on ' . 
                            optional($partner->token_used_at)->format('F j, Y \a\t g:i A'),
                'partner' => $partner
            ]);
        }

        // ✅ Do not mark the token as used here — just show the form
        return view('partner.register', compact('partner', 'token'));
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
            'title' => 'required|in:Brother,Sister,Deacon,Deaconess,Pastor',
            'designation' => 'required|in:Non-Pastoring,BLW Group Secretary,BLW Zonal Secretary,BLW Regional Secretary,Church Pastor,Sub-Group Pastor,Group Pastor,Asst. Zonal Pastor,Zonal Pastor,Zonal Director,Regional Pastor',
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
            'delivery_method' => 'nullable|string|max:1000',
            'coming_with_spouse' => 'boolean',
            'spouse_title' => 'nullable|required_if:coming_with_spouse,1|in:Brother,Sister,Deacon,Deaconess,Pastor',
            'spouse_name' => 'nullable|required_if:coming_with_spouse,1|string|max:255',
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

            return redirect()->route('partner.success');

        } catch (\Exception $e) {
            Log::error("Registration failed for token {$token}: " . $e->getMessage());
            
            return back()
                ->with('error', 'Registration failed. Please try again or contact support.')
                ->withInput();
        }
    }

    public function success(Partner $partner)
    {
         // Get the last registered partner for this session
        $partner = Partner::where('is_registered', true)
                        ->latest('token_used_at')
                        ->first();
                        
        if (!$partner) {
            return redirect()->route('partner.register', ['token' => request('token')])
                ->with('error', 'Session expired. Please try again.');
        }
        return view('partner.registration-success', compact('partner'));
    }
        /**
     * Verify partner via QR code scan
     */
    public function verify($id)
    {
        try {
            $partner = Partner::findOrFail($id);
            
            return view('partner.verification', compact('partner'));
            
        } catch (\Exception $e) {
            return view('partner.registration-invalid', [
                'message' => 'Invalid verification code. Please contact support.',
                'partner' => null
            ]);
        }
    }
}