<?php

namespace App\Http\Controllers;

use App\Models\PurchaseCategory;
use Illuminate\Http\Request;

class PurchaseCategoryController extends Controller
{
    public function index()
    {
        return PurchaseCategory::orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:purchase_categories,name',
        ]);

        $category = PurchaseCategory::create($data);

        return response()->json([
            'message' => 'Purchase category has been created',
            'category' => $category
        ]);
    }
}
