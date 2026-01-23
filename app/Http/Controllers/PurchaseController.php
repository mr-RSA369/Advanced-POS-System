<?php

namespace App\Http\Controllers;

use App\Models\BusinessDay;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{

    public function index(Request $request)
    {
        $query = Purchase::with('category');

        /* ================= DATE RANGE FILTER ================= */
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59',
            ]);
        }

        /* ================= MONTH FILTER ================= */ elseif ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('created_at', $request->month)
                ->whereYear('created_at', $request->year);
        }

        /* ================= YEAR FILTER ================= */ elseif ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')->get()
        );
    }



    public function store(Request $request)
    {
        try {

            /* ================= BUSINESS DAY ================= */
            $businessDay = BusinessDay::where('is_open', true)->first();

            if (!$businessDay) {
                return response()->json([
                    'message' => 'No business day is open'
                ], 400);
            }

            /* ================= DECODE ITEMS ================= */
            $items = json_decode($request->items, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'message' => 'Invalid items JSON format'
                ], 400);
            }

            if (!is_array($items) || count($items) === 0) {
                return response()->json([
                    'message' => 'At least one item is required'
                ], 422);
            }

            /* ================= BASIC VALIDATION ================= */
            $data = $request->validate([
                'purchase_category_id' => 'required|exists:purchase_categories,id',
                'bill_image'           => 'nullable|image',
                'business_day_id'      => 'required|exists:business_days,id',
            ]);

            /* ================= ITEMS VALIDATION ================= */
            foreach ($items as $index => $item) {

                if (empty($item['title'])) {
                    return response()->json([
                        'message' => "Item title is required at index {$index}"
                    ], 422);
                }

                if (!isset($item['price']) || !is_numeric($item['price'])) {
                    return response()->json([
                        'message' => "Valid price is required at index {$index}"
                    ], 422);
                }
            }

            /* ================= TOTAL CALCULATION ================= */
            $totalAmount = collect($items)->sum(function ($item) {
                return (float) ($item['price'] ?? 0);
            });

            /* ================= IMAGE UPLOAD ================= */
            if ($request->hasFile('bill_image')) {
                try {
                    $data['bill_image'] = $request->file('bill_image')
                        ->store('purchase-bills', 'public');
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Bill image upload failed: ' . $e->getMessage()
                    ], 500);
                }
            }

            /* ================= CREATE PURCHASE ================= */
            $purchase = Purchase::create([
                'purchase_category_id' => $data['purchase_category_id'],
                'items'                => $items,
                'total_bill'           => $totalAmount,
                'bill_image'           => $data['bill_image'] ?? null,
                'business_day_id'      => $businessDay->id,
            ]);

            return response()->json([
                'message'  => 'Purchase added successfully',
                'purchase' => $purchase->load('category'),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
}
