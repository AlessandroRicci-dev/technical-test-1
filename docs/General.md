# Project structure

## Folders

The project follows Laravel's standard conventions. Below are the most relevant directories:

```code

 ┣ 📂 app
 ┃ ┣ Http
 ┃ ┃ ┣ 📂 Models -> contains all the models
 ┃ ┃ ┣ 📂 Controllers -> contain all the controllers
 ┃ ┃ ┣ 📂 Request -> contain all the Validation class requests
 ┃ ┃ ┣ 📂 Services -> contain Api services
 ┣ 📂 database -> contain database migrations, seeders and data factory
 ┣ 📂 routes -> contains all the routes configured
 ┣ 📂 config -> a list of configs
 ┣ 📂 tests  -> Unit and feature testing
```

## Code architecture

The project uses a service-based architecture to manage orders. The main logic is contained in a dedicated controller:

app/Http/Controllers/Api/V1/OrderController.php

### OrderController

This controller is responsible for handling all order-related API requests. It imports and utilizes a dedicated service:

```code
private $orderService;

public function __construct(OrderService $orderService)
{
    $this->orderService = $orderService;
}
```

Each method in the controller is mapped in routes/api.php and:

Receives a specific request class (e.g., OrderIndexRequest).

Validates:

-   Permissions of the requesting user.

-   Data integrity.

-   User role, where applicable.

-   Delegates business logic to the OrderService.

### Order Service

The OrderService manages all order-related business logic, ensuring clean separation from controllers.

Responsibilities:

-   Listing, creating, updating, and deleting orders.

-   Managing stock adjustments.

-   Enforcing business rules and validations.

Future Improvements:

Refactor into specialized services:

-   OrderProcessorService -> Handles order creation and updates.

-   StockManagementService -> Manages stock changes.

-   OrderValidationService -> Enforces business rules.
