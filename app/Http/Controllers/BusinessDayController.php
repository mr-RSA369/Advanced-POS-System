<?php

namespace App\Http\Controllers;

use App\Models\BusinessDay;
use Illuminate\Http\Request;

class BusinessDayController extends Controller
{
    public function open_day()
    {
        if (BusinessDay::where('is_open', true)->exists()) {
            return response()->json([
                'message' => 'Business day already opened',
            ], 400);
        }

        if (BusinessDay::whereDate('business_date', now()->toDateString())->exists()) {
            $day = BusinessDay::where('is_open', false)
                ->whereDate('business_date', now()->toDateString())
                ->first();

            $day->update([
                'opened_at' => now(),
                'is_open' => true,
            ]);
        } else {
            $day = BusinessDay::create([
            'business_date' => now()->toDateString(),
            'opened_at' => now(),
            'is_open' => true,
        ]);
        }

        return response()->json($day);
    }

    public function close_day()
    {
        $day = BusinessDay::where('is_open', true)->first();

        if (!$day) {
            return response()->json([
                'message' => 'No business day is open',
            ], 400);
        }

        $day->update([
            'closed_at' => now(),
            'is_open' => false,
        ]);

        return response()->json([
            'message' => 'Business day closed',
            'day' => $day
        ]);
    }

    public function current()
    {
        $day = BusinessDay::where('is_open', true)->first();

        if (!$day) {
            return response()->json(null, 404);
        }

        return response()->json([
            'id' => $day->id,
            'business_date' => $day->business_date,
            'opened_at' => $day->opened_at,
        ]);
    }
}
