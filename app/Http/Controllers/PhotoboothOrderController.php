<?php

namespace App\Http\Controllers;

use App\Models\PhotoboothOrder;
use App\Services\PhotoDeliveryException;
use App\Services\SendPhotoboothPhotos;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PhotoboothOrderController extends Controller
{
    public function show(PhotoboothOrder $order): View
    {
        return view('photobooth.order', compact('order'));
    }

    public function resend(PhotoboothOrder $order, SendPhotoboothPhotos $sendPhotoboothPhotos): RedirectResponse
    {
        try {
            $sendPhotoboothPhotos->handle($order);
        } catch (PhotoDeliveryException $exception) {
            return redirect()
                ->route('photobooth.orders.show', $order->public_id)
                ->with('email_error', $exception->getMessage());
        }

        return redirect()
            ->route('photobooth.orders.show', $order->public_id)
            ->with('email_sent', 'Foto berhasil dikirim ulang. Periksa inbox dan folder spam.');
    }
}
