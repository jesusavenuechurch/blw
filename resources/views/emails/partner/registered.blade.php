@component('mail::message')
# Dear Esteemed **{{ $partner->title }} {{ $partner->full_name }}** - {{$partner->tier}} Partner 🎉

Warm greetings in the precious name of our Lord Jesus Christ!

Thank you for your unwavering partnership with Rhapsody of Realities in our year of completeness! We're excited to have you as our honoured guest at the Lounges - Angel Court for IPPC 2025.

## 🔗 Complete Your Registration

To finalize your registration and confirm your details, please click the button below:

@component('mail::button', ['url' => $registrationUrl, 'color' => 'success'])
Complete Registration
@endcomponent

## Your Partnership Details

- **Tier:** {{ $partner->tier_display }}
- **Email:** {{ $partner->email }}
@if($partner->region)
- **Region:** {{ $partner->region }}
@endif
@if($partner->ror_copies_sponsored)
- **ROR Copies Sponsored:** {{ $partner->ror_copies_sponsored }}
@endif


## What's Next?

1. **Click the registration button above** to complete your profile
2. **Review and update your information** if needed
3. **Confirm your IPPC attendance** and gift delivery preferences
4. **You'll receive your QR code** after completing registration

@if($partner->will_attend_ippc)
We're looking forward to seeing you at IPPC 2025! 🙏
@else
Your ROR gifts will be delivered as per your specified method.
@endif

---

**Need Help?**  
If you have any questions, please contact us at [support@angel-lounge.com](mailto:support@angel-lounge.com)

*This registration link is unique to you and will expire once used. Please do not share it with others.*

Thanks,  
{{ config('app.name') }}

@component('mail::subcopy')
If you're having trouble clicking the "Complete Registration" button, copy and paste this URL into your web browser:  
[{{ $registrationUrl }}]({{ $registrationUrl }})
@endcomponent

@endcomponent