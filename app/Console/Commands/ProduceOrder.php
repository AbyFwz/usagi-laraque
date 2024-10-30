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

        $channel->close();
        $connection->close();
    }
}
