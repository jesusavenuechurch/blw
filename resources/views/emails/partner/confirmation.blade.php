@component('mail::message')

<img src="{{ asset('images/logo.png') }}" alt="Angel Lounge Logo" width="120" style="display:block;margin:0 auto 20px;">

# Dear Esteemed **{{ $partner->title }} {{ $partner->full_name }}** - {{ucfirst($partner->tier)}} Partner

Thank you for completing your registration.  
We are delighted to have you as one of our esteemed partners and honoured guests at the **Angel Lounge**.

We look forward to sharing a **wonderful and memorable lounge experience** with you.

---

## Your Access QR Code

Please find your **unique QR Code** attached to this email — it will grant you access to the Angel Lounge.

---

## Need Assistance?

Should you have any questions or require further assistance, please don’t hesitate to reach out to us on **KingsChat handle – [@angellounges](https://kingschat.online/user/angellounges)**.

---

Warm regards,  
**Angel Lounges Team**

@component('mail::subcopy')
Your unique Angel Lounge QR code is attached. Please keep it safe for your lounge access at IPPC 2025.
@endcomponent
@endcomponent
