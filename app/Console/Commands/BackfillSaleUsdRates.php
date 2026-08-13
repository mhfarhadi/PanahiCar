<?php

namespace App\Console\Commands;

use App\Services\CurrencyRateService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:backfill-sale-usd-rates')]
#[Description('Backfill missing USD rate snapshots on historical sales')]
class BackfillSaleUsdRates extends Command
{
    public function handle(CurrencyRateService $currencyRateService): int
    {
        $sales = DB::table('sales')
            ->whereNull('usd_rate')
            ->orderBy('sale_date')
            ->orderBy('id')
            ->get([
                'id',
                'sale_date',
            ]);

        if ($sales->isEmpty()) {
            $this->info('No sales with missing USD rates.');

            return self::SUCCESS;
        }

        $updated = 0;
        $failed = 0;

        foreach ($sales as $sale) {
            $snapshot = $currencyRateService->snapshotForDate(
                'USD',
                $sale->sale_date
            );

            if (! $snapshot) {
                $failed++;

                $this->warn(
                    "Sale #{$sale->id}: USD rate not found for {$sale->sale_date}"
                );

                continue;
            }

            DB::table('sales')
                ->where('id', $sale->id)
                ->whereNull('usd_rate')
                ->update([
                    'usd_rate' => $snapshot['rate'],
                    'usd_rate_date' => $snapshot['rate_date'],
                    'usd_rate_source' => $snapshot['source'],
                    'updated_at' => now(),
                ]);

            $updated++;

            $this->line(
                "Sale #{$sale->id}: {$snapshot['rate']} ({$snapshot['source']})"
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
