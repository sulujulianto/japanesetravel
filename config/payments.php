<?php

$payloadRetentionDays = filter_var(env('PAYMENT_PAYLOAD_RETENTION_DAYS', 90), FILTER_VALIDATE_INT, [
    'options' => [
        'min_range' => 1,
        'max_range' => 3650,
    ],
]);

return [
    'payload_retention_days' => $payloadRetentionDays === false ? 90 : $payloadRetentionDays,
];
