<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MockOrder;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ProduceOrder extends Command
{
    protected $signature = 'produce:order';
    protected $description = 'Produce orders from the database to RabbitMQ every 10 seconds';

    public function handle()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        // Declare queue
        $channel->queue_declare('food_orders', false, true, false, false);

        // Fetch all orders from the database
        $orders = MockOrder::all();

        if (!$orders) {
            foreach ($orders as $order) {
                // Prepare message payload
                $orderData = [
                    'customer_id' => $order->customer_id,
                    'customer_name' => $order->customer_name,
                    'item_id' => $order->item_id,
                    'item_name' => $order->item_name,
                    'price' => $order->price,
                ];

                $message = new AMQPMessage(json_encode($orderData), ['delivery_mode' => 2]);
                $channel->basic_publish($message, '', 'food_orders');

                $this->info("Produced Order: " . json_encode($orderData));

                // Wait 10 seconds before sending the next message
                sleep(10);
            }
        } else {
            while (true) {
                // Generate a random order if data in mock_order table is empty
                $order = [
                    'customer_id' => rand(1000, 9999),
                    'customer_name' => 'Customer ' . rand(1, 100),
                    'item_id' => rand(1, 50),
                    'item_name' => 'Item ' . rand(1, 50),
                    'price' => rand(5, 100)
                ];
                $message = new AMQPMessage(json_encode($order), ['delivery_mode' => 2]);
                $channel->basic_publish($message, '', 'food_orders');

                $this->info("Produced Order: " . json_encode($order));
                sleep(10); // Send every 10 seconds
            }
        }


        $channel->close();
        $connection->close();
    }
}
