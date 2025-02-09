<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderDeleteRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Services\OrderService;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\OrderShowRequest;
use App\Http\Requests\OrderIndexRequest;
use App\Http\Requests\OrderStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OrderController extends Controller
{

    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {

        $orders = $this->orderService->list($request);
        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderStoreRequest $request): JsonResponse
    {
        $orders = $this->orderService->createNewOrder($request->validated());
        return response()->json($orders);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderShowRequest $request, string $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);
        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderStoreRequest $request, string $id): JsonResponse
    {

        $order = $this->orderService->update($request->validated(), $id);
        return response()->json($order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderDeleteRequest $request, string $id): JsonResponse
    {
        $this->orderService->deleteOrder($id);
        return response()->json(["status" => "deleted"]);
    }
}
