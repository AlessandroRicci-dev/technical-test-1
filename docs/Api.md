# API's

## Order API

Order Api lets you retrieve, store, update and delete Orders ensuring products stock levels to be consistent across concurrency.

### GET /order?query=''&fromDate={DATE}&toDate={DATE}

Let you retrieve through a full-search index service (Meilisearch) all orders, this api makes distinction between ADMIN and standard USER.
ADMIN can see all the orders while USER can see only their orders.
To prevent enourmous reponses this api is paginated, 20 results per page.

### GET /order/{id}

Let you retrieve a single product from the database, this api makes distinction between ADMIN and standard USER.
ADMIN can see all the orders while USER can see only their orders.

### POST /order

Let you insert a single order containing multiple products and balance stocks level.
To ensure non concurrency between multiple orders we largely used database transactions locking for update the product rows.
If an error is thrown, database will rolled back to the prev state.

In general you will always see some code like this:

```code
DB::beginTransaction();

try {

    $product = Product::lockForUpdate();

    .... other non concurrent operations

    DB::commit();

} catch (\Exception $e) {
    DB::rollBack();
    abort(400, $e->getMessage());
}
```

### PATCH /order/{id}

Let you update an existing order, USER are allowed to update the order as long as they are owner, ADMIN can always perform such operation.
To ensure correct balance stocks level and non concurrency we used the same approach as explained before, adding a difference detection system.
if some product are made available or new product are in the request, the system automatically set stock level by incereasing or decresing products stocks.

```code

$toDelete = $existingIds->diff($requestIds); -> made available
$toInsert = $requestIds->diff($existingIds); -> made unavailable
$commonIds = $existingIds->intersect($requestIds); -> check if qty are different

```

This algorithm can be for surely improved.

### DELETE /order/{id}

Let you delete an existing order, USER are allowed to delete the order as long as they are owner, ADMIN can always perform such operation.
This also adjust stocks level accordingly
