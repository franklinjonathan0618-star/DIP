<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePhotoboothOrderRequest;
use App\Services\CreatePhotoboothOrder;
use App\Services\PhotoDeliveryException;
use App\Services\SendPhotoboothPhotos;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PhotoboothCheckoutController extends Controller
{
    public function show(): View
    {
        return view('photobooth.checkout');
    }

    public function store(
        CreatePhotoboothOrderRequest $request,
        CreatePhotoboothOrder $createPhotoboothOrder,
        SendPhotoboothPhotos $sendPhotoboothPhotos,
    ): JsonResponse {
        $order = $createPhotoboothOrder->handle($request->validated());

        try {
            $sendPhotoboothPhotos->handle($order);
        } catch (PhotoDeliveryException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'order_url' => route('photobooth.orders.show', $order->public_id),
            ], 502);
        }

        return response()->json([
            'order_url' => route('photobooth.orders.show', $order->public_id),
        ], 201);
    }
}
