@php
    $muted = 'color:#6b7280;';
    $label = 'padding:8px 0; width:140px; vertical-align:top; color:#6b7280; font-size:13px;';
    $value = 'padding:8px 0; color:#111827; font-size:14px;';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Contact message</title>
</head>
<body style="margin:0; padding:24px 12px; background-color:#f3f4f6; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background-color:#ffffff; border-radius:12px; overflow:hidden;">

                <tr>
                    <td style="background-color:#111827; padding:24px 32px;">
                        <p style="margin:0; font-size:16px; font-weight:600; color:#ffffff;">New contact message</p>
                        <p style="margin:4px 0 0; font-size:13px; color:#9ca3af;">via {{ config('store.name') }} website</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:24px 32px 8px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="{{ $label }}">From</td>
                                <td style="{{ $value }}">{{ $data['name'] }}</td>
                            </tr>
                            <tr>
                                <td style="{{ $label }}">Email</td>
                                <td style="{{ $value }}"><a href="mailto:{{ $data['email'] }}" style="color:#111827;">{{ $data['email'] }}</a></td>
                            </tr>
                            @if (! empty($data['phone']))
                                <tr>
                                    <td style="{{ $label }}">Phone</td>
                                    <td style="{{ $value }}"><a href="tel:{{ $data['phone'] }}" style="color:#111827;">{{ $data['phone'] }}</a></td>
                                </tr>
                            @endif
                            @if (! empty($data['order_number']))
                                <tr>
                                    <td style="{{ $label }}">Order</td>
                                    <td style="{{ $value }} font-family:ui-monospace,Menlo,monospace;">{{ $data['order_number'] }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td style="{{ $label }}">Subject</td>
                                <td style="{{ $value }}">{{ $data['subject'] }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:8px 32px 28px;">
                        <p style="margin:0 0 8px; font-size:13px; {{ $muted }}">Message</p>
                        <div style="background-color:#f9fafb; border-radius:8px; padding:16px 20px; font-size:14px; line-height:1.7; color:#374151; white-space:pre-wrap;">{{ $data['message'] }}</div>
                    </td>
                </tr>

                <tr>
                    <td style="background-color:#f9fafb; padding:16px 32px; border-top:1px solid #e5e7eb;">
                        <p style="margin:0; font-size:12px; {{ $muted }}">
                            Reply directly to this email to respond to {{ $data['name'] }}.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
