<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

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

    // In your controller (e.g., StatsController.php)



/**
 * Get weekly sales stats (last 7 days)
 */
public function weeklySales()
{
    $startOfWeek = Carbon::now()->startOfWeek(); // Monday
    $endOfWeek = Carbon::now()->endOfWeek(); // Sunday

    $dailySales = Order::whereBetween('created_at', [$startOfWeek, $endOfWeek])
        ->where('status', 'closed')
        ->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('DAYNAME(created_at) as day_name'),
            DB::raw('SUM(net_bill) as total')
        )
        ->groupBy('date', 'day_name')
        ->orderBy('date', 'asc')
        ->get()
        ->keyBy('date');

    // Create array for all 7 days of the week
    $weekDays = [];
    for ($i = 0; $i < 7; $i++) {
        $date = $startOfWeek->copy()->addDays($i);
        $dateString = $date->format('Y-m-d');

        $weekDays[] = [
            'day' => $date->format('l'), // Full day name (Monday, Tuesday, etc.)
            'day_short' => $date->format('D'), // Short day name (Mon, Tue, etc.)
            'date' => $dateString,
            'total' => isset($dailySales[$dateString]) ? (int) $dailySales[$dateString]->total : 0,
            'has_sales' => isset($dailySales[$dateString])
        ];
    }

    return response()->json([
        'week_start' => $startOfWeek->format('Y-m-d'),
        'week_end' => $endOfWeek->format('Y-m-d'),
        'days' => $weekDays,
        'total' => array_sum(array_column($weekDays, 'total'))
    ]);
}

/**
 * Get today's sales
 */
public function dailySales()
{
    $today = Carbon::now()->startOfDay();
    $now = Carbon::now()->endOfDay();

    $totalSales = Order::whereBetween('created_at', [$today, $now])
        ->where('status', 'closed')
        ->sum('net_bill');

    // Get yesterday's sales for comparison
    $yesterday = Carbon::yesterday()->startOfDay();
    $yesterdayEnd = Carbon::yesterday()->endOfDay();

    $yesterdaySales = Order::whereBetween('created_at', [$yesterday, $yesterdayEnd])
        ->where('status', 'closed')
        ->sum('net_bill');

    // Calculate trend
    $trend = $yesterdaySales > 0
        ? round((($totalSales - $yesterdaySales) / $yesterdaySales) * 100, 1)
        : 0;

    // Get order count for today
    $orderCount = Order::whereBetween('created_at', [$today, $now])
        ->where('status', 'closed')
        ->count();

    return response()->json([
        'total' => (int) $totalSales,
        'order_count' => $orderCount,
        'trend' => $trend,
        'date' => Carbon::now()->format('Y-m-d'),
        'formatted_date' => Carbon::now()->format('l, F j'),
        'comparison' => [
            'yesterday' => (int) $yesterdaySales,
            'difference' => (int) ($totalSales - $yesterdaySales),
            'trend_percent' => $trend
        ]
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
