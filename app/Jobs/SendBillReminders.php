<?php

namespace App\Jobs;

use App\Models\Bill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBillReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get bills due in the next 3 days
        $upcomingBills = Bill::where('is_paid', false)
            ->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(3)])
            ->with('user')
            ->get();

        foreach ($upcomingBills as $bill) {
            // In a real application, you would send an email here
            // For now, we'll just log it
            Log::info('Bill reminder for user ' . $bill->user->username . ': ' . $bill->name . ' due on ' . $bill->due_date->format('Y-m-d'));
            
            // You could dispatch an email notification job here
            // Mail::to($bill->user->email)->send(new BillReminderMail($bill));
        }
        
        // Get overdue bills
        $overdueBills = Bill::where('is_paid', false)
            ->where('due_date', '<', Carbon::today())
            ->with('user')
            ->get();
        
        foreach ($overdueBills as $bill) {
            Log::warning('Overdue bill for user ' . $bill->user->username . ': ' . $bill->name . ' was due on ' . $bill->due_date->format('Y-m-d'));
        }
    }
}
