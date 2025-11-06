@component('mail::message')

<img src="{{ asset('images/logo.svg') }}" alt="Angel Lounge Logo" width="120" style="display:block;margin:0 auto 20px;">

# Dear Esteemed **{{ $partner->title }} {{ $partner->full_name }}** - {{ucfirst($partner->tier)}} Partner

Warm greetings in the precious name of our Lord Jesus Christ!

Thank you for your unwavering partnership with Rhapsody of Realities in our Year of Completeness! We're excited to have you as our honoured guest at the Angel Lounges - Angel Court for IPPC 2025.


@component('mail::button', ['url' => $registrationUrl, 'color' => 'success'])
Confirm My Details
@endcomponent


## What's Next?

1. **Click the registration button above** to complete your profile
2. **Review and update your information** if needed
3. **Confirm your IPPC attendance** and gift delivery preferences
4. **You'll receive your QR code** after completing registration


**Need Help?**  
If you have any questions, please contact us at 

[support@angel-lounge.com](mailto:support@angel-lounge.com)

*This registration link is unique to you and will expire once used. Please do not share it with others.*

Thanks, 
Angel Lounge Team.

@endcomponent