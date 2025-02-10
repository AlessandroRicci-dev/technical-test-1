<?php

namespace App\Http\Controllers\api\V1;


use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Services\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderShowRequest;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderDeleteRequest;
use App\Http\Requests\OrderIndexRequest;

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
    public function index(OrderIndexRequest $request): JsonResponse
    {
        $orders = $this->orderService->list($request->validated());
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
        $order = $this->orderService->getOrder($request->validated()['id']);
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
        $this->orderService->deleteOrder($request->validated()['id']);
        return response()->json(["status" => "deleted"]);
    }
}
