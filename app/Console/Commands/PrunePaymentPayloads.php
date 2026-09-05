<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use Illuminate\Console\Command;

class PrunePaymentPayloads extends Command
{
    protected $signature = 'payments:prune-payloads {--days= : Override the configured retention period}';

    protected $description = 'Remove expired payment payloads while preserving transaction and webhook audit records';

    public function handle(): int
    {
        $days = $this->retentionDays();
        if ($days === null) {
            $this->components->error('The retention period must be an integer between 1 and 3650 days.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $prunedEvents = PaymentWebhookEvent::query()
            ->where('created_at', '<', $cutoff)
            ->whereNotNull('payload_json')
            ->update([
                'payload_json' => null,
                'updated_at' => now(),
            ]);

        $prunedPayments = Payment::query()
            ->where('updated_at', '<', $cutoff)
            ->whereNotNull('payload_json')
            ->update([
                'payload_json' => null,
                'updated_at' => now(),
            ]);

        $this->components->info(
            "Pruned {$prunedPayments} payment payloads and {$prunedEvents} webhook payloads older than {$days} days."
        );

        return self::SUCCESS;
    }

    private function retentionDays(): ?int
    {
        $value = $this->option('days') ?? config('payments.payload_retention_days', 90);
        $days = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 1,
                'max_range' => 3650,
            ],
        ]);

        return $days === false ? null : (int) $days;
    }
}
