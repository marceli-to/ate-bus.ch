<?php

/*
|--------------------------------------------------------------------------
| Accident reports (Unfallmeldung)
|--------------------------------------------------------------------------
| Hidden pages: /unfallmeldung?k=CODE (staff), /unfall-fahrgast (passengers,
| QR code on the business card), /b and /unfallmeldung/b/{code} (other party).
*/

return [

    // Staff page is only reachable with this code in the link (?k=…). Empty = always 404.
    'code' => env('ACCIDENT_REPORTS_CODE'),

    // Who receives which report type
    'recipients' => [
        'vehicle' => env('ACCIDENT_REPORTS_EMAIL', 'info@ate-bus.ch'),
        'passenger' => env('ACCIDENT_REPORTS_EMAIL', 'info@ate-bus.ch'),
        'passenger_self' => env('ACCIDENT_REPORTS_EMAIL', 'info@ate-bus.ch'),
        'passenger_card' => env('ACCIDENT_REPORTS_EMAIL', 'info@ate-bus.ch'),
        'staff' => env('ACCIDENT_REPORTS_STAFF_EMAIL', 'personal@ate-bus.ch'),
    ],

    // Reply-To on the copy sent to the other party / passenger / employee
    'reply_to' => env('ACCIDENT_REPORTS_EMAIL', 'info@ate-bus.ch'),

    // Stored reports (entry, photos, report file) are deleted after this many days.
    // The mails in the mailboxes are not affected. Command: accident-reports:prune
    'retention_days' => (int) env('ACCIDENT_REPORTS_RETENTION_DAYS', 365),

    // How long the QR code for the other party stays valid
    'other_party_ttl_hours' => 12,

    // Upload limits per report
    'max_files' => 40,
    'max_file_kb' => 12 * 1024,
];
