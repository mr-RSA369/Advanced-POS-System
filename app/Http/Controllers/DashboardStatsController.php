<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Purchase;

class DashboardStatsController extends Controller
{
    /**
     * Total sales of running month (CLOSED ORDERS ONLY)
     */
    public function monthlySales()
    {
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $totalSales = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'closed')
            ->sum('net_bill');

        return response()->json([
            'total' => (int) $totalSales
        ]);
    }

    /**
     * Total purchases of running month
     */
    public function monthlyPurchases()
    {
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $totalPurchases = Purchase::whereBetween('created_at', [$start, $end])
            ->sum('total_bill');

        return response()->json([
            'total' => (int) $totalPurchases
        ]);
    }

    /**
     * Monthly order counts (CLOSED ORDERS ONLY)
     */
    public function monthlyOrders()
    {
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $baseQuery = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'closed');

        return response()->json([
            'delivery' => (int) (clone $baseQuery)->where('order_type', 'delivery')->count(),
            'dine_in'  => (int) (clone $baseQuery)->where('order_type', 'dine_in')->count(),
            'takeaway' => (int) (clone $baseQuery)->where('order_type', 'takeaway')->count(),
        ]);
    }
}
