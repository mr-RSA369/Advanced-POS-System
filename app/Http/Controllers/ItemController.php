<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => [
                'image',
                'dimensions:ratio=1:1',
            ],
            'category_id' => 'required',
            'name' => 'required',
            'price' => 'required',
            'status' => 'required',
            'description' => 'nullable|string',

        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item = Item::create($validated);

        response()->json([
            'message' => 'Item created successfully',
            'item' => $item
        ], 201);

        return redirect()->route('menu');
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'image' => [
                'image',
                'dimensions:ratio=1:1',
            ],
            'category_id' => 'required',
            'name' => 'required',
            'price' => 'required',
            'status' => 'required',
            'description' => 'nullable|string',

        ]);

        // handle image
        if ($request->hasFile('image')) {

            // delete old image
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $validated['image'] =
                $request->file('image')->store('items', 'public');
        }

        $item->update($validated);

        return response()->json([
            'message' => 'Item updated successfully',
            'item' => $item
        ]);
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        // delete image
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item deleted successfully'
        ]);
    }
}
