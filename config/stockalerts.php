<?php

return [
    'email_enabled' => (bool) env('STOCK_ALERT_EMAILS_ENABLED', false),

    // Stock that expires within this many days is flagged as "expiring soon".
    'expiry_warning_days' => (int) env('EXPIRY_WARNING_DAYS', 30),
];
