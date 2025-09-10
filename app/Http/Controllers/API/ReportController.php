<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ReportGenerationService;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Bill;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get spending report
     */
    public function spending(Request $request)
    {
        $user = $request->user();
        $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $reportService = app(ReportGenerationService::class);
        $report = $reportService->generateSpendingReport($user, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get income report
     */
    public function income(Request $request)
    {
        $user = $request->user();
        $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $reportService = app(ReportGenerationService::class);
        $report = $reportService->generateIncomeReport($user, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get savings report
     */
    public function savings(Request $request)
    {
        $user = $request->user();
        $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $reportService = app(ReportGenerationService::class);
        $report = $reportService->generateSavingsReport($user, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get comprehensive financial report
     */
    public function comprehensive(Request $request)
    {
        $user = $request->user();
        $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $reportService = app(ReportGenerationService::class);
        $report = $reportService->generateComprehensiveReport($user, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Export report data
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $format = $request->get('format', 'csv');
        $type = $request->get('type', 'comprehensive');
        $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $exportService = app(ExportService::class);
        
        switch ($type) {
            case 'transactions':
                $result = $exportService->exportTransactionsToCsv($user, [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);
                break;
            case 'budgets':
                $result = $exportService->exportBudgetsToCsv($user, Carbon::parse($startDate)->format('Y-m'));
                break;
            case 'savings_goals':
                $result = $exportService->exportSavingsGoalsToCsv($user);
                break;
            case 'bills':
                $result = $exportService->exportBillsToCsv($user);
                break;
            case 'comprehensive':
            default:
                $result = $exportService->exportComprehensiveDataToCsv($user, [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Export completed successfully',
            'data' => $result
        ]);
    }

    /**
     * Get spending data for reports
     */
    private function getSpendingData($user, $startDate, $endDate)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
    }

    /**
     * Get income data for reports
     */
    private function getIncomeData($user, $startDate, $endDate)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
    }

    /**
     * Get budget data for reports
     */
    private function getBudgetData($user, $startDate, $endDate)
    {
        $month = Carbon::parse($startDate)->format('Y-m');
        
        return Budget::where('user_id', $user->id)
            ->whereRaw('DATE_FORMAT(month_year, "%Y-%m") = ?', [$month])
            ->get();
    }

    /**
     * Get bills data for reports
     */
    private function getBillsData($user)
    {
        return [
            'upcoming' => Bill::where('user_id', $user->id)
                ->where('is_paid', false)
                ->where('due_date', '>=', now())
                ->count(),
            'overdue' => Bill::where('user_id', $user->id)
                ->where('is_paid', false)
                ->where('due_date', '<', now())
                ->count(),
            'total_amount_due' => Bill::where('user_id', $user->id)
                ->where('is_paid', false)
                ->sum('amount')
        ];
    }
}
