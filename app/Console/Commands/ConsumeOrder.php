<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Illuminate\Database\QueryException;

class ConsumeOrder extends Command
{
    protected $signature = 'consume:order';
    protected $description = 'Consume food order messages from RabbitMQ';

    public function handle()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('food_orders', false, true, false, false);

        $callback = function ($msg) {
            try {
                $orderData = json_decode($msg->body, true);

                // Check for existing order based on unique fields
                $existingOrder = Order::where('customer_id', $orderData['customer_id'])
                    ->where('item_id', $orderData['item_id'])
                    ->first();

                if ($existingOrder) {
                    $this->info("Duplicate order ignored: " . json_encode($orderData));
                    $msg->ack(); // Acknowledge the message to avoid reprocessing
                    return;
                }

                // Store the order in the database
                Order::create([
                    'customer_id' => $orderData['customer_id'],
                    'customer_name' => $orderData['customer_name'],
                    'item_id' => $orderData['item_id'],
                    'item_name' => $orderData['item_name'],
                    'price' => $orderData['price'],
                ]);

                $this->info("Order stored: " . json_encode($orderData));
                $msg->ack(); // Acknowledge the message

            } catch (QueryException $e) {
                // Handle unique constraint violation error
                $this->error("Duplicate order detected due to constraint: " . json_encode($orderData));
                $msg->ack(); // Acknowledge the message

            } catch (\Exception $e) {
                // Log other errors without acknowledging, to allow RabbitMQ to retry
                $this->error("Failed to store order: " . $e->getMessage());
            }
        };

        $channel->basic_consume('food_orders', '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
