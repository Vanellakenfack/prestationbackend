<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminStatsService;
use Illuminate\Http\JsonResponse;

class AdminStatsController extends Controller
{
    public function __construct(
        private AdminStatsService $statsService
    ) {
        $this->middleware(['auth:api', 'role:admin']);
    }

    /**
     * @OA\Get(
     *     path="/admin/stats",
     *     summary="Récupère les statistiques administratives",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques administratives",
     *         @OA\JsonContent(
     *             @OA\Property(property="users", type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="new_today", type="integer"),
     *                 @OA\Property(property="by_type", type="object")
     *             ),
     *             @OA\Property(property="transactions", type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="revenue_today", type="number"),
     *                 @OA\Property(property="pending", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'users' => $this->statsService->getUserStats(),
            'transactions' => $this->statsService->getTransactionStats(),
            'system' => $this->statsService->getSystemStats()
        ]);
    }
}