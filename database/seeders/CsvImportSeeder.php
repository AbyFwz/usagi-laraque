<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class CsvImportSeeder extends Seeder
{
    public function run()
    {
        $csv = Reader::createFromPath(storage_path() . '\data\indonesian_food_orders.csv', 'r');
        $csv->setHeaderOffset(0); //set the CSV header offset

        foreach ($csv as $record) {
            DB::table('mock_orders')->insert([
                // customer_id,customer_name,item_id,item_name,price
                'customer_id' => $record['customer_id'],
                'customer_name' => $record['customer_name'],
                'item_id' => $record['item_id'],
                'item_name' => $record['item_name'],
                'price' => $record['price'],
            ]);
        }
    }
}
