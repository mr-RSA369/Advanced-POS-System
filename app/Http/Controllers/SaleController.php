<?php

namespace App\Http\Controllers;

use App\Models\BusinessDay;
use App\Models\Order;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function daily(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $businessDay = BusinessDay::whereDate('business_date', $request->date)->first();

        if (!$businessDay) {
            return response()->json([
                'date' => $request->date,
                'total_sale' => 0,
                'order_count' => 0,
            ]);
        }

        $stats = Order::where('status', 'closed')
            ->where('business_day_id', $businessDay->id)
            ->selectRaw('
            COALESCE(SUM(net_bill), 0) as total_sale,
            COUNT(*) as order_count
        ')
            ->first();

        return response()->json([
            'date' => $businessDay->business_date,
            'total_sale' => (int) $stats->total_sale,
            'order_count' => (int) $stats->order_count,
        ]);
    }


    public function index(Request $request)
    {
        $query = BusinessDay::query();

        /* ================= DATE RANGE FILTER ================= */
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('business_date', [
                $request->from_date,
                $request->to_date,
            ]);
        }

        /* ================= MONTH FILTER ================= */ elseif ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('business_date', $request->month)
                ->whereYear('business_date', $request->year);
        }

        /* ================= YEAR FILTER ================= */ elseif ($request->filled('year')) {
            $query->whereYear('business_date', $request->year);
        }

        $sales = $query
            ->orderBy('business_date', 'desc')
            ->get()
            ->map(function ($day) {

                $stats = Order::where('status', 'closed')
                    ->where('business_day_id', $day->id)
                    ->selectRaw('
                    COALESCE(SUM(net_bill), 0) as total_sale,
                    COUNT(*) as order_count
                ')
                    ->first();

                return [
                    'date' => $day->business_date,
                    'total_sale' => (int) $stats->total_sale,
                    'order_count' => (int) $stats->order_count,
                ];
            });

        return response()->json($sales);
    }
}
