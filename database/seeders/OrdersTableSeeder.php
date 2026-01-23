<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::create([
            'order_id'       =>  'DL-0000000',
            'total_bill'     =>   0,
            'order_type'     =>   null,
            'items'          =>   json_encode("xyz"),
            'status'         =>  'nothing',
            'customer_phone' =>  '000000',
        ]);
    }
}
