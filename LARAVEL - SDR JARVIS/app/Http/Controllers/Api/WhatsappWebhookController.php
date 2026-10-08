<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsappWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class WhatsappWebhookController extends Controller
{
    /**
     * Endpoint receptor de Webhooks da Evolution API.
     */
    public function handle(Request $request, WhatsappWebhookService $service, ?string $instance = null): JsonResponse
    {
        try {
            $payload = $request->all();
            $result = $service->handle($payload, $instance);

            return response()->json($result);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
