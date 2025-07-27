<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use App\Services\ModerationLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminModerationController extends Controller
{
    public function __construct(
        private ModerationLogService $logService
    ) {
        $this->middleware(['auth:api', 'role:admin']);
    }

    /**
     * @OA\Put(
     *     path="/admin/services/{id}/approve",
     *     summary="Approuver un service",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du service",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service approuvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service non trouvé"
     *     )
     * )
     */
    public function approveService(int $id): JsonResponse
    {
        $service = Service::findOrFail($id);

        $service->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        $this->logService->log(
            auth()->id(),
            'service_approval',
            $service->id,
            ['status' => 'approved']
        );

        return response()->json(['message' => 'Service approuvé avec succès']);
    }

    /**
     * @OA\Delete(
     *     path="/admin/users/{id}",
     *     summary="Supprimer un utilisateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Utilisateur supprimé"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Action non autorisée"
     *     )
     * )
     */
    public function deleteUser(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Empêche la suppression d'un admin par un autre admin
        if ($user->hasRole('admin') && !$request->user()->hasRole('super-admin')) {
            abort(403, 'Action non autorisée');
        }

        $user->delete();

        $this->logService->log(
            auth()->id(),
            'user_deletion',
            $user->id,
            ['email' => $user->email]
        );

        return response()->json(null, 204);
    }
}