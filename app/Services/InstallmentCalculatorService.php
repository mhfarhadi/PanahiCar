<?php

namespace App\Services;

use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class InstallmentCalculatorService
{
    public function calculate(
        int $salePrice,
        int $downPayment,
        float $monthlyProfitRate,
        int $installmentCount,
        string $saleDate,
        string $firstDueDate
    ): array {
        $principal = $salePrice - $downPayment;

        $deferment = $this->calculateDeferment(
            $saleDate,
            $firstDueDate
        );

        $baseInstallmentProfit = (int) round(
            $principal
            * ($monthlyProfitRate / 100)
            * $installmentCount
        );

        $defermentEquivalentMonths =
            $deferment['months'] + ($deferment['days'] / 30);

        $defermentProfit = (int) round(
            $principal
            * ($monthlyProfitRate / 100)
            * $defermentEquivalentMonths
        );

        $calculatedInstallmentProfit =
            $baseInstallmentProfit + $defermentProfit;

        $calculatedInstallmentTotal =
            $principal + $calculatedInstallmentProfit;

        $installmentAmount = (int) (
            round(
                ($calculatedInstallmentTotal / $installmentCount) / 10_000
            ) * 10_000
        );

        $installmentTotal =
            $installmentAmount * $installmentCount;

        $installmentProfit = max(
            0,
            $installmentTotal - $principal
        );

        $contractTotal =
            $downPayment + $installmentTotal;

        return [
            'principal' => $principal,
            'monthly_profit_rate' => $monthlyProfitRate,
            'installment_count' => $installmentCount,
            'base_installment_profit' => $baseInstallmentProfit,
            'deferment_months' => $deferment['months'],
            'deferment_days' => $deferment['days'],
            'deferment_profit' => $defermentProfit,
            'standard_first_due_date' => $deferment['standard_first_due_date'],
            'first_due_date' => $firstDueDate,
            'installment_amount' => $installmentAmount,
            'installment_total' => $installmentTotal,
            'installment_profit' => $installmentProfit,
            'contract_total' => $contractTotal,
            'installments' => $this->buildInstallments(
                $firstDueDate,
                $installmentCount,
                $installmentAmount
            ),
        ];
    }

    public function calculateCustom(
        int $salePrice,
        int $downPayment,
        float $monthlyProfitRate,
        string $saleDate,
        array $payments
    ): array {
        $principal = $salePrice - $downPayment;
        $balance = $principal;
        $totalPaid = 0;
        $totalProfit = 0;
        $previousDate = $saleDate;
        $calculatedPayments = [];

        foreach ($payments as $index => $payment) {
            $dueDate = (string) $payment['due_date'];
            $amount = (int) $payment['amount'];

            $interval = $this->calculateInterval(
                $previousDate,
                $dueDate
            );

            $equivalentMonths =
                $interval['months'] + ($interval['days'] / 30);

            $profit = (int) round(
                $balance
                * ($monthlyProfitRate / 100)
                * $equivalentMonths
            );

            $balanceBeforePayment = $balance + $profit;
            $balance = $balanceBeforePayment - $amount;

            $totalPaid += $amount;
            $totalProfit += $profit;

            $calculatedPayments[] = [
                'installment_number' => $index + 1,
                'due_date' => $dueDate,
                'amount' => $amount,
                'balance_before' => $balanceBeforePayment - $profit,
                'interval_months' => $interval['months'],
                'interval_days' => $interval['days'],
                'equivalent_months' => $equivalentMonths,
                'profit' => $profit,
                'balance_after' => $balance,
            ];

            $previousDate = $dueDate;
        }

        return [
            'principal' => $principal,
            'monthly_profit_rate' => $monthlyProfitRate,
            'payments' => $calculatedPayments,
            'total_paid' => $totalPaid,
            'total_profit' => $totalProfit,
            'remaining_balance' => $balance,
        ];
    }

    public function findWithinMonthlyCap(
        int $salePrice,
        int $downPayment,
        float $monthlyProfitRate,
        int $monthlyCap,
        string $saleDate,
        string $firstDueDate,
        int $maxInstallments = 60
    ): ?array {
        for ($count = 1; $count <= $maxInstallments; $count++) {
            $result = $this->calculate(
                $salePrice,
                $downPayment,
                $monthlyProfitRate,
                $count,
                $saleDate,
                $firstDueDate
            );

            if ($result['installment_amount'] <= $monthlyCap) {
                return $result;
            }
        }

        return null;
    }

    public function calculateDeferment(
        string $saleDate,
        string $firstDueDate
    ): array {
        $saleJalali = Jalalian::fromCarbon(
            Carbon::parse($saleDate)->startOfDay()
        );

        $standardJalali = $saleJalali->addMonths(1);
        $standardCarbon = $standardJalali->toCarbon()->startOfDay();
        $chosenCarbon = Carbon::parse($firstDueDate)->startOfDay();

        if ($chosenCarbon->lt($standardCarbon)) {
            return [
                'is_before_standard' => true,
                'standard_first_due_date' => $standardCarbon->toDateString(),
                'months' => 0,
                'days' => 0,
            ];
        }

        $months = 0;
        $cursorJalali = $standardJalali;

        while (true) {
            $nextJalali = $cursorJalali->addMonths(1);
            $nextCarbon = $nextJalali->toCarbon()->startOfDay();

            if ($nextCarbon->gt($chosenCarbon)) {
                break;
            }

            $months++;
            $cursorJalali = $nextJalali;
        }

        $cursorCarbon = $cursorJalali->toCarbon()->startOfDay();
        $days = (int) floor(
            $cursorCarbon->diffInDays($chosenCarbon)
        );

        return [
            'is_before_standard' => false,
            'standard_first_due_date' => $standardCarbon->toDateString(),
            'months' => $months,
            'days' => $days,
        ];
    }

    private function calculateInterval(
        string $startDate,
        string $endDate
    ): array {
        $startJalali = Jalalian::fromCarbon(
            Carbon::parse($startDate)->startOfDay()
        );

        $endCarbon = Carbon::parse($endDate)->startOfDay();
        $startCarbon = $startJalali->toCarbon()->startOfDay();

        if ($endCarbon->lt($startCarbon)) {
            throw new \InvalidArgumentException(
                'Payment due dates must be chronological.'
            );
        }

        $months = 0;
        $cursorJalali = $startJalali;

        while (true) {
            $nextJalali = $cursorJalali->addMonths(1);
            $nextCarbon = $nextJalali->toCarbon()->startOfDay();

            if ($nextCarbon->gt($endCarbon)) {
                break;
            }

            $months++;
            $cursorJalali = $nextJalali;
        }

        $cursorCarbon = $cursorJalali->toCarbon()->startOfDay();
        $days = (int) floor(
            $cursorCarbon->diffInDays($endCarbon)
        );

        return [
            'months' => $months,
            'days' => $days,
        ];
    }

    private function buildInstallments(
        string $firstDueDate,
        int $count,
        int $amount
    ): array {
        $firstDueJalali = Jalalian::fromCarbon(
            Carbon::parse($firstDueDate)
        );

        $installments = [];

        for ($i = 0; $i < $count; $i++) {
            $installments[] = [
                'installment_number' => $i + 1,
                'due_date' => $i === 0
                    ? $firstDueJalali->toCarbon()->toDateString()
                    : $firstDueJalali
                        ->addMonths($i)
                        ->toCarbon()
                        ->toDateString(),
                'amount' => $amount,
            ];
        }

        return $installments;
    }
}
