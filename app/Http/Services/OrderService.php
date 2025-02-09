<?php

namespace App\Http\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class OrderService
{
    /**
     * List orders with pagination.
     * If the logged-in user is a "USER", only orders belonging to that user are returned.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request)
    {
        $orders = Order::with('products')
            ->when($request->user()->role === "USER", function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->paginate(20);

        return $orders;
    }

    /**
     * Create a new order and update the product stock accordingly.
     *
     * @param array $orderDTO Array containing order details and products.
     * @return Order
     */
    public function createNewOrder(array $orderDTO)
    {
        DB::beginTransaction();

        try {
            // Create the order with provided details.
            $order = Order::create([
                'user_id'     => $orderDTO["user_id"],
                'name'        => $orderDTO["name"],
                'description' => $orderDTO["description"],
                'status'      => $orderDTO["status"],
            ]);

            // For each product in the request, update the stock and create the associated OrderItem.
            foreach ($orderDTO['products'] as $orderProduct) {
                // Lock the product record for update to avoid race conditions.
                $product = Product::lockForUpdate()->findOrFail($orderProduct['id']);

                // Use helper to decrement the stock, aborting if stock is insufficient.
                $this->adjustProductStock($product, $orderProduct['qty'], true);

                // Create the OrderItem linked to the order.
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $orderProduct['qty'],
                    'unit_price' => $product->price,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            abort(400, $e->getMessage());
        }

        return $order;
    }

    /**
     * Update an order using computed differences between existing OrderItems and request products.
     *
     * @param array $request The request data containing products.
     * @param int $id The order ID.
     * @return Order
     */
    public function update(array $request, int $id)
    {
        DB::beginTransaction();

        try {
            // 1. Load the order with its related OrderItems and index them by product_id.
            $order = Order::with('orderItems')->findOrFail($id);
            $existingItems = $order->orderItems->keyBy('product_id');

            // 2. Collect the products from the request and index them by id.
            $requestProducts = collect($request['products'])->keyBy('id');

            // 3. Calculate the differences between existing OrderItems and request products.
            list($toDelete, $toInsert, $toUpdate) = $this->computeDifferences($existingItems, $requestProducts);

            // 4. Update the OrderItems of the order.
            $this->updateOrderItems($order, $toDelete, $toInsert, $toUpdate, $requestProducts);

            // 5. Update the stock of the products, ensuring that stock does not drop below 0.
            $this->updateStock($existingItems, $toDelete, $toInsert, $toUpdate, $requestProducts);

            $order->update([
                'name'        => $request["name"] ?? $order->name,
                'description' => $request["description"] ?? $order->description,
                'status'      => $request["status"] ?? $order->status,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $order;
    }

    /**
     * Retrieve an order along with its associated products.
     *
     * @param string $id The order ID.
     * @return Order|null
     */
    public function getOrder(string $id)
    {
        return Order::with('products')->where("id", $id)->first();
    }


    /**
     * Delete an order and restore the stock of its associated products.
     *
     * @param string $id The order ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOrder(string $id)
    {
        DB::beginTransaction();

        try {
            $order = Order::with('orderItems')->findOrFail($id);
            // For each OrderItem, restore the product's stock.
            foreach ($order->orderItems as $orderItem) {
                $product = Product::lockForUpdate()->findOrFail($orderItem->product_id);
                $product->stock_quantity += $orderItem->quantity;
                $product->save();
            }

            // Delete all OrderItems associated with the order.
            OrderItem::where('order_id', $order->id)->delete();

            // Delete the order.
            $order->delete();

            DB::commit();
            return response()->json(['message' => 'Order deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Adjust the stock of a product.
     *
     * If $decrease is true, decrement the stock and abort if insufficient.
     * Otherwise, increment the stock by the specified quantity.
     *
     * @param Product $product The product to adjust.
     * @param int $quantity The quantity to adjust by.
     * @param bool $decrease Whether to decrease (true) or increase (false) the stock.
     */
    private function adjustProductStock(Product $product, int $quantity, bool $decrease = true)
    {
        if ($decrease) {
            if ($product->stock_quantity < $quantity) {
                abort(400, "Stock insufficient for product ID: {$product->id}");
            }
            // Decrement the stock.
            $product->decrement('stock_quantity', $quantity);
        } else {
            // Increment the stock.
            $product->increment('stock_quantity', $quantity);
        }
    }

    /**
     * Calculate differences between the existing OrderItems and the request products.
     *
     * Returns three collections:
     * - $toDelete: OrderItems to delete (exist in DB but not in request).
     * - $toInsert: OrderItems to insert (exist in request but not in DB).
     * - $toUpdate: OrderItems to update (common items with different quantities).
     *
     * @param \Illuminate\Support\Collection $existingItems Existing OrderItems indexed by product_id.
     * @param \Illuminate\Support\Collection $requestProducts Request products indexed by id.
     * @return array
     */
    private function computeDifferences($existingItems, $requestProducts): array
    {
        $existingIds = $existingItems->keys();
        $requestIds  = $requestProducts->keys();

        // OrderItems to delete: exist in DB but not in request.
        $toDelete = $existingIds->diff($requestIds);

        // OrderItems to insert: exist in request but not in DB.
        $toInsert = $requestIds->diff($existingIds);

        // OrderItems common to both collections.
        $commonIds = $existingIds->intersect($requestIds);

        // For common items, check if the quantity is different.
        $toUpdate = collect();
        foreach ($commonIds as $productId) {
            $existingQty = $existingItems[$productId]->quantity;
            $newQty      = $requestProducts[$productId]['qty'];
            if ($existingQty != $newQty) {
                $toUpdate->put($productId, ['old' => $existingQty, 'new' => $newQty]);
            }
        }

        return [$toDelete, $toInsert, $toUpdate];
    }

    /**
     * Update the OrderItems of an order based on the calculated differences.
     *
     * @param Order $order The order to update.
     * @param \Illuminate\Support\Collection $toDelete OrderItems to delete.
     * @param \Illuminate\Support\Collection $toInsert OrderItems to insert.
     * @param \Illuminate\Support\Collection $toUpdate OrderItems to update.
     * @param \Illuminate\Support\Collection $requestProducts Request products indexed by id.
     */
    private function updateOrderItems($order, $toDelete, $toInsert, $toUpdate, $requestProducts)
    {
        // Delete OrderItems that are not present in the request.
        if ($toDelete->isNotEmpty()) {
            $order->orderItems()->whereIn('product_id', $toDelete->all())->delete();
        }

        // Insert new OrderItems.
        foreach ($toInsert as $productId) {
            $order->orderItems()->create([
                'product_id' => $productId,
                'quantity'   => $requestProducts[$productId]['qty']
            ]);
        }

        // Update common OrderItems with changed quantities.
        foreach ($toUpdate as $productId => $data) {
            $order->orderItems()
                ->where('product_id', $productId)
                ->update(['quantity' => $data['new']]);
        }
    }

    /**
     * Update the stock of products based on the operations performed on OrderItems.
     *
     * Ensures that the stock does not drop below 0; aborts with an error if necessary.
     *
     * @param \Illuminate\Support\Collection $existingItems Existing OrderItems indexed by product_id.
     * @param \Illuminate\Support\Collection $toDelete OrderItems to delete.
     * @param \Illuminate\Support\Collection $toInsert OrderItems to insert.
     * @param \Illuminate\Support\Collection $toUpdate OrderItems to update.
     * @param \Illuminate\Support\Collection $requestProducts Request products indexed by id.
     */
    private function updateStock($existingItems, $toDelete, $toInsert, $toUpdate, $requestProducts)
    {
        // a. For each deletion: increment the product's stock.
        $toDelete->each(function ($productId) use ($existingItems) {
            $qtyRemoved = $existingItems[$productId]->quantity;
            $product = Product::lockForUpdate()->findOrFail($productId);
            $product->increment('stock_quantity', $qtyRemoved);
        });

        // b. For each insertion: decrement the product's stock.
        $toInsert->each(function ($productId) use ($requestProducts) {
            $qtyInserted = $requestProducts[$productId]['qty'];
            $product = Product::lockForUpdate()->findOrFail($productId);
            if ($product->stock_quantity < $qtyInserted) {
                abort(400, "Stock insufficient for product ID: {$productId}");
            }
            $product->decrement('stock_quantity', $qtyInserted);
        });

        // c. For updates: adjust the product's stock based on the difference.
        foreach ($toUpdate as $productId => $data) {
            $difference = $data['new'] - $data['old'];
            $product = Product::lockForUpdate()->findOrFail($productId);
            if ($difference > 0) {
                if ($product->stock_quantity < $difference) {
                    abort(400, "Stock insufficient for product ID: {$productId}");
                }
                $product->decrement('stock_quantity', $difference);
            } else {
                $product->increment('stock_quantity', abs($difference));
            }
        }
    }
}
