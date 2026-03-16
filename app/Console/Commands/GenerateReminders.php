<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:generate-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and queue reminders for expiring orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();
        $warningDate = now()->addDays(7)->startOfDay();

        // Find orders expiring in the next 7 days, or where reminder date is today or passed
        $orders = \App\Models\Order::whereIn('status', ['active', 'expiring_soon'])
            ->where(function($query) use ($today, $warningDate) {
                $query->whereDate('expiry_date', '<=', $warningDate)
                      ->orWhereDate('reminder_date', '<=', $today);
            })
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            /** @var \App\Models\Order $order */
            // Determine type
            $type = 'upcoming_expiry';
            if ($order->expiry_date && $order->expiry_date->isPast()) {
                $type = 'expired';
                $order->update(['status' => 'expired']);
            } elseif ($order->reminder_date && $order->reminder_date <= $today) {
                $type = 'custom';
            } else {
                if ($order->status !== 'expiring_soon') {
                    $order->update(['status' => 'expiring_soon']);
                }
            }

            // Prevent duplicate pending reminders for the same order/type
            $existing = \App\Models\Reminder::where('order_id', $order->id)
                ->where('type', $type)
                ->where('status', 'pending')
                ->exists();

            if (!$existing) {
                \App\Models\Reminder::create([
                    'client_id' => $order->client_id,
                    'order_id' => $order->id,
                    'type' => $type,
                    'status' => 'pending',
                    'trigger_date' => $today,
                ]);
                $count++;
            }
        }

        $this->info("Generated $count new reminders.");
    }
}
