<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payments')
            ->whereIn('status', ['paid', 'failed', 'expired', 'refunded'])
            ->whereNotNull('payload_json')
            ->update(['payload_json' => null]);

        DB::table('payments')
            ->where('status', 'pending')
            ->whereNotNull('payload_json')
            ->select(['id', 'provider', 'provider_ref', 'payload_json'])
            ->orderBy('id')
            ->chunkById(500, function ($payments): void {
                foreach ($payments as $payment) {
                    $payload = json_decode((string) $payment->payload_json, true);
                    $payload = is_array($payload) ? $payload : [];
                    $redirectUrl = $this->redirectUrl($payload);
                    $sanitized = [];

                    if ($redirectUrl !== null) {
                        $sanitized['gateway'] = [
                            'provider' => mb_substr((string) $payment->provider, 0, 255),
                            'provider_ref' => mb_substr((string) $payment->provider_ref, 0, 255),
                            'redirect_url' => $redirectUrl,
                        ];
                    }

                    $integrityFailure = $this->integrityFailure($payload);
                    if ($integrityFailure !== []) {
                        $sanitized['capture_integrity_error'] = $integrityFailure;
                    }

                    DB::table('payments')
                        ->where('id', $payment->id)
                        ->update([
                            'payload_json' => $sanitized === []
                                ? null
                                : json_encode($sanitized, JSON_THROW_ON_ERROR),
                        ]);
                }
            });

        DB::table('payment_webhook_events')
            ->whereNotNull('payload_json')
            ->update(['payload_json' => null]);
    }

    public function down(): void
    {
        // Redacted secrets and personal data cannot be reconstructed safely.
    }

    /** @param array<string, mixed> $payload */
    private function redirectUrl(array $payload): ?string
    {
        $gateway = is_array($payload['gateway'] ?? null) ? $payload['gateway'] : [];
        $candidate = $gateway['redirect_url']
            ?? $gateway['payment_url']
            ?? $payload['redirect_url']
            ?? $payload['payment_url']
            ?? null;

        if (is_string($candidate) && $candidate !== '') {
            return mb_substr($candidate, 0, 2048);
        }

        $links = is_array($gateway['links'] ?? null) ? $gateway['links'] : [];
        foreach ($links as $link) {
            if (! is_array($link) || ($link['rel'] ?? null) !== 'approve') {
                continue;
            }

            $href = $link['href'] ?? null;
            if (is_string($href) && $href !== '') {
                return mb_substr($href, 0, 2048);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    private function integrityFailure(array $payload): array
    {
        $legacy = is_array($payload['capture_integrity_error'] ?? null)
            ? $payload['capture_integrity_error']
            : [];
        $allowed = [];

        foreach ([
            'expected_provider_ref',
            'received_provider_ref',
            'expected_amount',
            'received_amount',
            'expected_currency',
            'received_currency',
        ] as $key) {
            $value = $legacy[$key] ?? null;
            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);
            if ($value !== '') {
                $allowed[$key] = mb_substr($value, 0, 255);
            }
        }

        return $allowed;
    }
};
