<?php

namespace App\Livewire\ClientPortal;

use Livewire\Component;
use App\Models\Order;
use App\Models\Client;
use App\Models\ClientBalanceTransaction;

class PortalDashboard extends Component
{
    public function render()
    {
        $client = Client::where('user_id', auth()->id())->first();

        if (!$client) {
            return view('livewire.client-portal.portal-dashboard', ['client' => null])->layout('layouts.app');
        }

        $orders = Order::where('client_id', $client->id)->get();

        $kpis = [
            'active_products'  => 0,
            'expiring_soon'    => 0,
            'expired'          => 0,
            'total_paid'       => 0,
            'total_pending'    => 0,
            'cashback_earned'  => 0,
            'cashback_pending' => 0,
        ];

        foreach ($orders as $order) {
            $status = $order->dynamic_status;

            if (in_array($status, ['Active', 'Payment Pending'])) {
                $kpis['active_products']++;
            }
            if ($status === 'Expiring Soon') {
                $kpis['expiring_soon']++;
                $kpis['active_products']++;
            }
            if ($status === 'Expired') {
                $kpis['expired']++;
            }

            $kpis['total_paid']    += $order->paid_amount;
            $kpis['total_pending'] += $order->pending_amount;

            // Cashback totals
            if ($order->cashback_enabled_snapshot) {
                if ($order->cashback_rewarded) {
                    $kpis['cashback_earned'] += (float) $order->cashback_amount;
                } else {
                    $kpis['cashback_pending'] += (float) $order->cashback_amount;
                }
            }
        }

        // Full cashback history for the wallet section
        $cashbackHistory = ClientBalanceTransaction::where('client_id', $client->id)
            ->where('type', 'cashback_reward')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('livewire.client-portal.portal-dashboard', [
            'client'          => $client,
            'kpis'            => $kpis,
            'cashbackHistory' => $cashbackHistory,
        ])->layout('layouts.app');
    }
}
