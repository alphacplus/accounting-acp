<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemHealthService
{
    public function check(): array
    {
        $start = microtime(true);

        // Database status
        $dbStatus = [
            'connected' => false,
            'latency_ms' => 0,
            'error' => null,
        ];

        try {
            DB::connection()->getPdo();
            $dbStatus['connected'] = true;
            $dbStatus['latency_ms'] = round((microtime(true) - $start) * 1000, 2);
        } catch (\Exception $e) {
            $dbStatus['error'] = $e->getMessage();
        }

        // Core accounting counts
        $counts = [
            'accounts' => 0,
            'journal_entries' => 0,
            'customers' => 0,
            'vendors' => 0,
            'bank_accounts' => 0,
            'periods' => 0,
        ];

        // Safely count records in tables that may not exist yet
        if (Schema::hasTable('accounts')) {
            $counts['accounts'] = DB::table('accounts')->count();
        }

        if (Schema::hasTable('journal_entries')) {
            $counts['journal_entries'] = DB::table('journal_entries')->count();
        }

        if (Schema::hasTable('customers')) {
            $counts['customers'] = DB::table('customers')->count();
        }

        if (Schema::hasTable('vendors')) {
            $counts['vendors'] = DB::table('vendors')->count();
        }

        if (Schema::hasTable('bank_accounts')) {
            $counts['bank_accounts'] = DB::table('bank_accounts')->count();
        }

        if (Schema::hasTable('periods')) {
            $counts['periods'] = DB::table('periods')->count();
        }

        // Double-entry trial balance check
        $trialBalance = [
            'total_debit' => 0,
            'total_credit' => 0,
            'difference' => 0,
            'is_balanced' => false,
        ];

        if (Schema::hasTable('journal_lines')) {
            try {
                $result = DB::table('journal_lines')
                    ->select(
                        DB::raw('SUM(debit) as total_debit'),
                        DB::raw('SUM(credit) as total_credit')
                    )
                    ->first();

                $trialBalance['total_debit'] = (float) ($result->total_debit ?? 0);
                $trialBalance['total_credit'] = (float) ($result->total_credit ?? 0);
                $trialBalance['difference'] = abs($trialBalance['total_debit'] - $trialBalance['total_credit']);
                $trialBalance['is_balanced'] = $trialBalance['difference'] < 0.01;
            } catch (\Exception $e) {
                // If there's an error in the trial balance calculation, keep defaults
            }
        }

        // Open vs closed periods
        $periods = [
            'open' => 0,
            'closed' => 0,
        ];

        if (Schema::hasTable('periods')) {
            try {
                $periodCounts = DB::table('periods')
                    ->select(
                        DB::raw('COUNT(CASE WHEN closed = 0 THEN 1 END) as open'),
                        DB::raw('COUNT(CASE WHEN closed = 1 THEN 1 END) as closed')
                    )
                    ->first();

                $periods['open'] = (int) ($periodCounts->open ?? 0);
                $periods['closed'] = (int) ($periodCounts->closed ?? 0);
            } catch (\Exception $e) {
                // If there's an error in period counts, keep defaults
            }
        }

        return [
            'timestamp' => Carbon::now()->toISOString(),
            'database' => $dbStatus,
            'counts' => $counts,
            'trial_balance' => $trialBalance,
            'periods' => $periods,
        ];
    }
}
