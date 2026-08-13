<?php

namespace App\Console\Commands;

use App\Services\CurrencyRateService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:backfill-purchase-usd-rates')]
#[Description('Backfill missing USD rate snapshots on historical purchases')]
class BackfillPurchaseUsdRates extends Command
{
    public function handle(CurrencyRateService $currencyRateService): int
    {
        $purchases = DB::table('purchases')
            ->whereNull('usd_rate')
            ->orderBy('purchase_date')
            ->orderBy('id')
            ->get([
                'id',
                'purchase_date',
            ]);

        if ($purchases->isEmpty()) {
            $this->info('No purchases with missing USD rates.');

            return self::SUCCESS;
        }

        $updated = 0;
        $failed = 0;

        foreach ($purchases as $purchase) {
            $snapshot = $currencyRateService->snapshotForDate(
                'USD',
                $purchase->purchase_date
            );

            if (! $snapshot) {
                $failed++;

                $this->warn(
                    "Purchase #{$purchase->id}: USD rate not found for {$purchase->purchase_date}"
                );

                continue;
            }

            DB::table('purchases')
                ->where('id', $purchase->id)
                ->whereNull('usd_rate')
                ->update([
                    'usd_rate' => $snapshot['rate'],
                    'usd_rate_date' => $snapshot['rate_date'],
                    'usd_rate_source' => $snapshot['source'],
                    'updated_at' => now(),
                ]);

            $updated++;

            $this->line(
                "Purchase #{$purchase->id}: {$snapshot['rate']} ({$snapshot['source']})"
            );
        }

        $this->newLine();

        $this->info("Updated: {$updated}");

        if ($failed > 0) {
            $this->warn("Missing: {$failed}");
        }

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
