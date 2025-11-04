@component('mail::message')
# 🎉 Registration Complete!

Dear **{{ $partner->title }} {{ $partner->full_name }}**,

Congratulations! Your IPPC 2025 partner registration has been successfully completed.

## ✅ Confirmed Details

- **Partnership Tier:** {{ $partner->tier_display }}
- **Email:** {{ $partner->email }}
@if($partner->phone)
- **Phone:** {{ $partner->phone }}
@endif
@if($partner->region)
- **Region:** {{ $partner->region }}
@endif
@if($partner->church)
- **Church:** {{ $partner->church }}
@endif
@if($partner->ror_copies_sponsored)
- **ROR Copies Sponsored:** {{ $partner->ror_copies_sponsored }}
@endif

@if($partner->coming_with_spouse)
## 👥 Spouse Information

You registered with your spouse:
- **{{ $partner->spouse_title }} {{ $partner->spouse_name }} {{ $partner->spouse_surname }}**
@if($partner->spouse_kc_handle)
- **KC Handle:** {{ $partner->spouse_kc_handle }}
@endif
@endif

## 📱 Your QR Code is Attached!

Your unique, tier-specific QR code is attached to this email. **This is important!**

**What you can do with your QR code:**
- ✅ Quick check-in at IPPC 2025
- ✅ Verification of your partnership status
- ✅ Access to partner-exclusive areas and benefits
- ✅ Fast-track entry at the ROR exhibition

**Please save this QR code:**
- Download and save it to your phone
- Print a copy for backup
- Take a screenshot for easy access

@if($partner->will_attend_ippc)
## 🎊 IPPC 2025 Attendance

Great news! You've confirmed your attendance at IPPC 2025.

@if($partner->will_be_at_exhibition)
**Exhibition:** You'll also be at the ROR exhibition at Angel Court. We look forward to seeing you there!
@else
**Note:** You won't be attending the exhibition, but you can still enjoy all other IPPC activities.
@endif

**Remember to bring:**
- Your QR code (digital or printed)
- Valid ID
- Your excitement and expectation!

@else
## 📦 ROR Gift Delivery

Since you won't be attending IPPC 2025, your ROR gifts will be delivered as follows:

**Delivery Method:** {{ $partner->delivery_method ?? 'To be confirmed' }}

We'll contact you separately to coordinate the delivery.
@endif

## 🌟 What Happens Next?

1. **Save your QR code** from this email attachment
2. **Watch your email** for IPPC updates and schedules
3. **Mark your calendar** for IPPC 2025
@if($partner->will_attend_ippc)
4. **Prepare for an amazing experience** at IPPC!
@endif

## 📞 Need Assistance?

If you need to update any information or have questions:
- **Email:** [support@angel-lounge.com](mailto:support@angel-lounge.com)
- **Reply to this email** and we'll get back to you

---

Thank you for being a valued partner in spreading God's Word through the Rhapsody of Realities! 

Your partnership makes a global impact. 🙏

**God bless you richly!**

Thanks,  
{{ config('app.name') }} Team

@component('mail::subcopy')
This email confirms your completed registration for IPPC 2025. Your QR code is attached and ready to use. Keep it safe!
@endcomponent

@endcomponent