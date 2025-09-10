<?php

namespace App\Services;

use App\Models\User;
use App\Models\Budget;
use App\Models\Bill;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send budget alerts to user
     */
    public function sendBudgetAlerts(User $user, string $month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        
        $budgetService = app(BudgetCalculationService::class);
        $alerts = $budgetService->getBudgetAlerts($user, $month);

        foreach ($alerts as $alert) {
            $this->sendNotification($user, 'budget_alert', $alert);
        }

        return $alerts;
    }

    /**
     * Send bill reminders
     */
    public function sendBillReminders(User $user, int $daysAhead = 3)
    {
        $upcomingBills = Bill::where('user_id', $user->id)
            ->where('is_paid', false)
            ->whereBetween('due_date', [now(), now()->addDays($daysAhead)])
            ->with('category')
            ->get();

        $overdueBills = Bill::where('user_id', $user->id)
            ->where('is_paid', false)
            ->where('due_date', '<', now())
            ->with('category')
            ->get();

        $reminders = [];

        // Upcoming bills
        foreach ($upcomingBills as $bill) {
            $daysUntilDue = now()->diffInDays(Carbon::parse($bill->due_date), false);
            $reminders[] = $this->sendNotification($user, 'bill_reminder', [
                'bill_id' => $bill->id,
                'bill_name' => $bill->name,
                'amount' => $bill->amount,
                'due_date' => $bill->due_date,
                'days_until_due' => $daysUntilDue,
                'category_name' => $bill->category->name,
                'message' => "Bill '{$bill->name}' is due in {$daysUntilDue} days ($" . number_format($bill->amount, 2) . ")",
            ]);
        }

        // Overdue bills
        foreach ($overdueBills as $bill) {
            $daysOverdue = now()->diffInDays(Carbon::parse($bill->due_date));
            $reminders[] = $this->sendNotification($user, 'bill_overdue', [
                'bill_id' => $bill->id,
                'bill_name' => $bill->name,
                'amount' => $bill->amount,
                'due_date' => $bill->due_date,
                'days_overdue' => $daysOverdue,
                'category_name' => $bill->category->name,
                'message' => "Bill '{$bill->name}' is {$daysOverdue} days overdue ($" . number_format($bill->amount, 2) . ")",
            ]);
        }

        return $reminders;
    }

    /**
     * Send savings goal notifications
     */
    public function sendSavingsGoalNotifications(User $user)
    {
        $goals = SavingsGoal::where('user_id', $user->id)
            ->where('is_completed', false)
            ->get();

        $notifications = [];

        foreach ($goals as $goal) {
            $percentage = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
            $daysRemaining = Carbon::parse($goal->target_date)->diffInDays(now());

            // Goal completion notification
            if ($percentage >= 100 && !$goal->is_completed) {
                $notifications[] = $this->sendNotification($user, 'goal_completed', [
                    'goal_id' => $goal->id,
                    'goal_name' => $goal->name,
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'message' => "Congratulations! You've completed your savings goal '{$goal->name}'!",
                ]);
            }
            // Goal milestone notifications (25%, 50%, 75%)
            elseif (in_array(floor($percentage / 25) * 25, [25, 50, 75]) && $percentage > 0) {
                $milestone = floor($percentage / 25) * 25;
                $notifications[] = $this->sendNotification($user, 'goal_milestone', [
                    'goal_id' => $goal->id,
                    'goal_name' => $goal->name,
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'percentage' => $percentage,
                    'milestone' => $milestone,
                    'message' => "Great progress! You've reached {$milestone}% of your '{$goal->name}' goal!",
                ]);
            }
            // Goal deadline approaching
            elseif ($daysRemaining <= 7 && $daysRemaining > 0) {
                $notifications[] = $this->sendNotification($user, 'goal_deadline', [
                    'goal_id' => $goal->id,
                    'goal_name' => $goal->name,
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'days_remaining' => $daysRemaining,
                    'message' => "Your savings goal '{$goal->name}' deadline is in {$daysRemaining} days!",
                ]);
            }
        }

        return $notifications;
    }

    /**
     * Send spending pattern alerts
     */
    public function sendSpendingPatternAlerts(User $user, string $month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        
        $budgetService = app(BudgetCalculationService::class);
        $recommendations = $budgetService->getBudgetRecommendations($user, $month);

        $alerts = [];

        foreach ($recommendations as $recommendation) {
            if ($recommendation['recommendation']['type'] !== 'info') {
                $alerts[] = $this->sendNotification($user, 'spending_pattern', [
                    'category_id' => $recommendation['category_id'],
                    'current_amount' => $recommendation['current_amount'],
                    'previous_amount' => $recommendation['previous_amount'],
                    'change_percentage' => $recommendation['change_percentage'],
                    'recommendation' => $recommendation['recommendation'],
                    'message' => $recommendation['recommendation']['message'],
                ]);
            }
        }

        return $alerts;
    }

    /**
     * Send low balance alerts
     */
    public function sendLowBalanceAlerts(User $user, float $threshold = 100.00)
    {
        $accounts = $user->accounts()->where('is_active', true)->get();
        $alerts = [];

        foreach ($accounts as $account) {
            if ($account->balance < $threshold) {
                $alerts[] = $this->sendNotification($user, 'low_balance', [
                    'account_id' => $account->id,
                    'account_name' => $account->name,
                    'account_type' => $account->type,
                    'current_balance' => $account->balance,
                    'threshold' => $threshold,
                    'message' => "Low balance alert: {$account->name} has $" . number_format($account->balance, 2) . " (below $" . number_format($threshold, 2) . ")",
                ]);
            }
        }

        return $alerts;
    }

    /**
     * Send monthly summary notification
     */
    public function sendMonthlySummary(User $user, string $month = null)
    {
        $month = $month ?? Carbon::now()->subMonth()->format('Y-m');
        
        $dashboardService = app(DashboardDataService::class);
        $monthlyData = $dashboardService->getMonthlySpending($user, $month);
        $budgetData = $dashboardService->getBudgetProgress($user, $month);

        $summary = [
            'month' => $month,
            'total_income' => $monthlyData['total_income'],
            'total_spent' => $monthlyData['total_spent'],
            'net_flow' => $monthlyData['net_flow'],
            'budget_utilization' => $budgetData['percentage_used'],
            'budget_status' => $budgetData['percentage_used'] > 100 ? 'exceeded' : 'on_track',
        ];

        return $this->sendNotification($user, 'monthly_summary', [
            'summary' => $summary,
            'message' => "Monthly Summary for " . Carbon::parse($month . '-01')->format('F Y') . ": " .
                        "Income: $" . number_format($summary['total_income'], 2) . ", " .
                        "Spent: $" . number_format($summary['total_spent'], 2) . ", " .
                        "Net: $" . number_format($summary['net_flow'], 2) . ", " .
                        "Budget: " . number_format($summary['budget_utilization'], 1) . "% used",
        ]);
    }

    /**
     * Send all pending notifications for a user
     */
    public function sendAllNotifications(User $user)
    {
        $notifications = [];

        // Budget alerts
        $notifications = array_merge($notifications, $this->sendBudgetAlerts($user));

        // Bill reminders
        $notifications = array_merge($notifications, $this->sendBillReminders($user));

        // Savings goal notifications
        $notifications = array_merge($notifications, $this->sendSavingsGoalNotifications($user));

        // Spending pattern alerts
        $notifications = array_merge($notifications, $this->sendSpendingPatternAlerts($user));

        // Low balance alerts
        $notifications = array_merge($notifications, $this->sendLowBalanceAlerts($user));

        return $notifications;
    }

    /**
     * Send notification to user
     */
    private function sendNotification(User $user, string $type, array $data)
    {
        $notification = [
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data,
            'message' => $data['message'] ?? 'Notification',
            'created_at' => now(),
        ];

        // Log notification (in a real app, this would be stored in database or sent via email/push)
        Log::info('Notification sent', $notification);

        // In a real implementation, you would:
        // 1. Store in notifications table
        // 2. Send email notification
        // 3. Send push notification
        // 4. Send in-app notification

        return $notification;
    }

    /**
     * Get user's notification preferences
     */
    public function getUserNotificationPreferences(User $user)
    {
        // This would typically come from user settings
        return [
            'budget_alerts' => true,
            'bill_reminders' => true,
            'savings_goals' => true,
            'spending_patterns' => true,
            'low_balance' => true,
            'monthly_summary' => true,
            'email_notifications' => true,
            'push_notifications' => false,
        ];
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId)
    {
        // This would update the notification status in the database
        Log::info('Notification marked as read', ['notification_id' => $notificationId]);
    }

    /**
     * Get unread notifications for user
     */
    public function getUnreadNotifications(User $user, int $limit = 10)
    {
        // This would query the notifications table
        // For now, return empty array
        return [];
    }
}
