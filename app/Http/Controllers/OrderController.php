<?php

namespace App\Http\Controllers;

use App\Models\BusinessDay;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;


class OrderController extends Controller
{

    public function storeBill(Request $request, Order $order)
    {
        $order->update([
            'discount'         => $request->discount ?? 0,
            'service_charges'  => $request->service_charges ?? 0,
            'delivery_charges' => $request->delivery_charges ?? 0,
            'net_bill'         => $request->net_bill,
        ]);

        try {
            $connector = new WindowsPrintConnector("smb://DESKTOP-I6PFT17/BIXOLON");
            $printer = new Printer($connector);
            try {
                // ================= HEADER =================
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("BROZ PIZZA & CAFE\n");
                $printer->selectPrintMode();
                $printer->text(str_repeat("-", 32) . "\n");

                // ================= ORDER INFO =================
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("ORDER ID   : #" . str_pad($order->order_id, 8) . "\n");
                $printer->text("DATE       : " . date('d/m/Y H:i') . "\n");
                $printer->text("TYPE       : " . strtoupper($order->order_type) . "\n");

                if ($order->order_type === 'Dine-in' && $order->table_name) {
                    $printer->text("TABLE      : " . strtoupper($order->table_name) . "\n");
                }

                if ($order->customer_phone) {
                    $printer->text("PHONE      : " . $order->customer_phone . "\n");
                }

                $printer->text(str_repeat("-", 32) . "\n");

                // ================= ITEMS =================

                foreach ($order->items as $item) {
                    $itemName = substr($item['item_name'], 0, 22); // Limit name length

                    // For wrapped lines
                    $lines = explode("\n", wordwrap($itemName . "(" . $item['type'] . ")", 22, "\n", true));

                    foreach ($lines as $index => $line) {
                        if ($index === 0) {

                            $lineText = str_pad($line, 24);

                            $qtyText = str_pad("x" . $item['qty'], 5);
                            $priceText = str_pad("Rs." . number_format($item['line_total'], 0), 10, ' ', STR_PAD_LEFT);
                            $printer->setFont(Printer::FONT_B);
                            $printer->text($lineText . $qtyText . $priceText . "\n");
                        } else {
                            $printer->text($line . "\n");
                        }
                    }

                    $printer->text("\n");
                }

                $printer->text(str_repeat("-", 32) . "\n");
                $printer->setFont(Printer::FONT_A);
                // ================= TOTALS =================
                $printer->setJustification(Printer::JUSTIFY_RIGHT);


                $printer->text("TOTAL   : RS." . str_pad($order->total_bill, 8) . "\n");

                // DISCOUNT
                if ($order->discount > 0) {

                    $printer->text("DISCOUNT   : -RS." . str_pad($order->discount, 8) . "\n");
                }

                // SERVICE CHARGES
                if ($order->service_charges > 0) {


                    $printer->text("SERVICE CHARGES   : RS." . str_pad($order->service_charges, 8) . "\n");
                }


                // DELIVERY CHARGES
                if ($order->delivery_charges > 0) {

                    $printer->text("DELIVERY CHARGES   : RS." . str_pad($order->delivery_charges, 8) . "\n");
                }

                $printer->text(str_repeat("=", 32) . "\n");

                $printer->setEmphasis(true);



                $printer->text("NET TOTAL   : RS." . str_pad($order->net_bill, 8) . "\n");

                $printer->setEmphasis(false);

                $printer->text(str_repeat("=", 32) . "\n");

                // CASH & CHANGE

                $printer->text("CASH RECEIVED   : RS." . str_pad($request->cash, 8) . "\n");


                $printer->text("CHANGE        : RS." . str_pad($request->change, 8) . "\n");

                // ================= FOOTER =================
                $printer->feed(1);
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("THANK YOU!\n");
                $printer->text("VISIT AGAIN\n\n");
                $printer->text("Hassanabad Gate No.1\n");

                $printer->feed(1);
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->setFont(Printer::FONT_B);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cant get items',
                    'data' => $request,
                    'error' => $e->getMessage()
                ], 500);
            }
            $printer->text("Powered by: So Code\n");
            $printer->feed(2);

            // Cut paper
            $printer->cut();

            // Close connection
            $printer->close();

            return response()->json([
                'status' => true,
                'message' => 'Printed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Printer not available or printing failed',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json(
            ['message' => "bill stored successfully!"],
            201
        );
    }

    public function printKitchenBill(Request $request)
    {
        $order = $request->all();

        try {
            $connector = new WindowsPrintConnector("smb://DESKTOP-I6PFT17/BIXOLON");
            $printer = new Printer($connector);

            try {
                // ================= HEADER =================
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->setEmphasis(true);
                $printer->text("BROZ PIZZA & CAFE\n");
                $printer->setEmphasis(false);
                $printer->selectPrintMode();
                $printer->text(str_repeat("-", 32) . "\n");

                // ================= ORDER INFO =================
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("ORDER ID   : #" . str_pad($order['order_id'] ?? '', 8, ' ', STR_PAD_LEFT) . "\n");
                $printer->text("DATE       : " . date('d/m/Y H:i') . "\n");
                $printer->text("TYPE       : " . strtoupper($order['order_type'] ?? '') . "\n");

                if (($order['order_type'] ?? '') === 'Dine-in' && isset($order['table_name'])) {
                    $printer->text("TABLE      : " . strtoupper($order['table_name']) . "\n");
                }

                if (isset($order['customer_phone'])) {
                    $printer->text("PHONE      : " . $order['customer_phone'] . "\n");
                }

                $printer->text(str_repeat("-", 32) . "\n");

                // ================= ITEMS =================
                if (isset($order['items']) && is_array($order['items'])) {
                    foreach ($order['items'] as $item) {
                        if (!is_array($item)) {
                            continue;
                        }

                        $itemName = $item['item_name'] ?? '';
                        $itemType = $item['type'] ?? '';
                        $qty = $item['qty'] ?? 0;
                        $lineTotal = $item['line_total'] ?? 0;

                        $itemName = substr($itemName, 0, 22);

                        // For wrapped lines
                        $itemText = $itemName . ($itemType ? "(" . $itemType . ")" : "");
                        $lines = explode("\n", wordwrap($itemText, 22, "\n", true));

                        foreach ($lines as $index => $line) {
                            if ($index === 0) {

                                $lineText = str_pad($line, 24);
                                $qtyText = str_pad("x" . $qty, 5);
                                $priceText = str_pad("Rs." . number_format($lineTotal, 0), 10, ' ', STR_PAD_LEFT);

                                $printer->setFont(Printer::FONT_B);
                                $printer->text($lineText . $qtyText . $priceText . "\n");
                                $printer->setFont(Printer::FONT_A);
                            } else {

                                $printer->text($line . "\n");
                            }
                        }

                        // Add a small space between items
                        $printer->text("\n");
                    }
                } else {
                    $printer->text("No items found\n");
                }

                $printer->feed(1);
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->setFont(Printer::FONT_B);
                $printer->text("Powered by: So Code\n");
                $printer->setFont(Printer::FONT_A);
                $printer->feed(2);

                // Cut paper
                $printer->cut();

                // Close connection
                $printer->close();

                return response()->json([
                    'status' => true,
                    'message' => 'Printed successfully'
                ]);
            } catch (\Exception $e) {
                $printer->close();
                return response()->json([
                    'status' => false,
                    'message' => 'Cant get items',
                    'data' => $request->all(),
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Printer not available or printing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function order_id()
    {
        $order_id = Order::latest('id')->value('order_id');

        $id = substr($order_id, 3);

        return response()->json($id);
    }

    public function store(Request $request)
    {
        $businessDay = BusinessDay::where('is_open', true)->first();

        if (!$businessDay) {
            return response()->json([
                'message' => 'No business day is open'
            ], 400);
        }

        $order = Order::create([
            'order_id' => $request['order_id'],
            'order_type' => $request['order_type'],
            'items' => $request['items'],
            'total_bill' => $request['total_bill'],
            'discount' => $request['discount'],
            'service_charges' => $request['service_charges'],
            'delivery_charges' => $request['delivery_charges'],
            'net_bill' => $request['net_bill'],
            'status' => $request['status'],
            'customer_phone' => $request['customer_phone'],
            'table_no' => $request['table_no'],
            'business_day_id' => $businessDay->id,
        ]);

        return response()->json([
            'message' => 'Order placed and bill generated',
            'order'   => $order,
        ], 201);
    }

    public function index(Request $request)
    {
        $query = Order::with('businessDay')
            ->where('status', $request->status);

        /* DATE RANGE */
        if ($request->filled(['from_date', 'to_date'])) {
            $query->whereHas('businessDay', function ($q) use ($request) {
                $q->whereBetween('business_date', [
                    $request->from_date,
                    $request->to_date
                ]);
            });
        }

        /* ORDER TYPE */
        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        /* ITEM SEARCH */
        if ($request->filled('item')) {
            $query->whereRaw(
                "LOWER(items) LIKE ?",
                ['%' . strtolower($request->item) . '%']
            );
        }

        return response()->json([
            'data' => $query->latest()->get()
        ]);
    }

    public function update(Request $request, Order $order)
    {
        // 1. Validate incoming status
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['created', 'closed', 'cancelled']),
            ],
        ]);

        // 2. Prevent invalid transitions
        $currentStatus = $order->status;
        $newStatus = $validated['status'];

        $allowedTransitions = [
            'created'   => ['closed', 'cancelled'],
            'closed'    => ['cancelled'],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            return response()->json([
                'message' => 'Invalid status transition',
            ], 422);
        }

        // 3. Update status
        $order->status = $newStatus;
        $order->save();

        // 4. Return updated order
        return response()->json([
            'message' => 'Order status updated successfully',
            'data' => $order->fresh(),
        ]);
    }
}
