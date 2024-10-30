Here’s a sample `README.md` for your project:

---

# RabbitMQ Laravel Producer-Consumer Example

This project demonstrates a basic producer-consumer architecture using **Laravel** and **RabbitMQ**. The producer service retrieves orders from a database and publishes them as messages to RabbitMQ every 10 seconds, while a consumer service listens to the queue and stores these messages in a database if they are unique.

## Requirements

- **Docker** (for running RabbitMQ) or RabbitMQ as a local service
- **Laravel** (PHP framework)
- **MySQL** or any other database compatible with Laravel

## Setup and Installation

1. **Clone the Repository**

   ```bash
   git clone <repository-url>
   cd rabbitmq-laravel-demo
   ```

2. **Install Laravel Dependencies**

   ```bash
   composer install
   ```

3. **Configure Environment Variables**

   Rename the `.env.example` file to `.env` and update the following variables:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

4. **Run Migrations**

   ```bash
   php artisan migrate
   ```

5. **Start RabbitMQ with Docker**

   Create a `docker-compose.yml` file for RabbitMQ:
   ```yaml
   version: '3.8'
   services:
     rabbitmq:
       image: rabbitmq:3-management
       ports:
         - '5672:5672'
         - '15672:15672'
       environment:
         RABBITMQ_DEFAULT_USER: guest
         RABBITMQ_DEFAULT_PASS: guest
   ```

   Start RabbitMQ:
   ```bash
   docker-compose up -d
   ```

6. **Seed Orders Data (Optional)**

   Add some sample data to the `orders` table by creating and running a Laravel seeder, or manually insert records in the database for testing.

## Producer Service

The producer fetches each order from the database and sends it to the RabbitMQ queue every 10 seconds. To run the producer:

```bash
php artisan produce:order
```

### Producer Details

- **Fetches** all records from the `orders` table.
- **Publishes** each order to the RabbitMQ `food_orders` queue in JSON format.
- **Waits 10 seconds** before sending the next order.

## Consumer Service

The consumer listens to the `food_orders` queue and processes each message. It checks for duplicates and saves unique orders to the database.

To run the consumer:

```bash
php artisan consume:order
```

### Consumer Details

- **Listens** to the `food_orders` queue.
- **Processes** each message by decoding and saving it to the `orders` table.
- **Error Handling**:
  - Checks if the order already exists (based on `customer_id` and `item_id`) to prevent duplicates.
  - Logs errors if a message can’t be processed.

## Key Design Choices

- **RabbitMQ** was chosen for message queueing to enable reliable message delivery and support for acknowledgment.
- **Database Constraints** ensure that duplicate orders are not stored. Unique constraints on `customer_id` and `item_id` prevent duplicate records.
- **Consumer Retry Strategy**: If a duplicate is detected or another error occurs, the consumer will log the issue without re-processing the message.

## Accessing the RabbitMQ Management UI

Once RabbitMQ is running, access the management UI at `http://localhost:15672` using:

- Username: `guest`
- Password: `guest`

## Improvements

Given more time, the following improvements could be made:

1. **Implement Message Acknowledgments**: Only acknowledge messages in RabbitMQ once they are successfully processed and stored in the database.
2. **Add Docker for Laravel**: Containerize the entire Laravel application, including the database, for easier deployment and management.
3. **Add Frontend for Monitoring and Logging**: Add a frontend for monitoring and logging. This could be done using a web framework like **ReactJS** or **Laravel Blade**.

---

This setup provides a simple yet effective system to handle real-time order processing with RabbitMQ and Laravel!
