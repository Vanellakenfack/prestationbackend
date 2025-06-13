<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Middleware pour vérifier les droits admin
     */
    public function __construct()
    {
    $this->middleware(['auth:sanctum', 'admin'])->except('login');
    }

    // ==========================
    // AUTHENTIFICATION ADMIN
    // ==========================

public function login(Request $request)
{
    // Validation spécifique admin
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Vérification des credentials
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Identifiants incorrects'
        ], 401);
    }

    // Vérification du type admin
    if ($user->type !== 'admin') {
        return response()->json([
            'message' => 'Accès réservé aux administrateurs'
        ], 403);
    }

    // Création du token sans device_name
    $token = $user->createToken('admin-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'type' => $user->type
        ]
    ]);
}
    public function createAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);

        $admin = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'admin'
        ]);

        return response()->json([
            'message' => 'Administrateur créé avec succès',
            'admin' => $admin
        ], 201);
    }

    // ==========================
    // UTILISATEURS
    // ==========================
    public function listUsers(Request $request)
    {
        $users = User::with(['prestataire', 'client'])
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($users);
    }

    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'type' => ['required', Rule::in(['admin', 'prestataire', 'client'])],
            // Champs conditionnels
            'metier' => 'required_if:type,prestataire',
            'bio' => 'nullable|string',
            'adresse' => 'required_if:type,client',
            'telephone' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'type' => $request->type
        ]);

        // Création du profil spécifique
        if ($request->type === 'prestataire') {
            $user->prestataire()->create($request->only(['metier', 'bio']));
        } elseif ($request->type === 'client') {
            $user->client()->create($request->only(['adresse', 'telephone']));
        }

        return response()->json($user->load(['prestataire', 'client']), 201);
    }

    public function updateUser(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'type' => ['sometimes', Rule::in(['admin', 'prestataire', 'client'])],
            'status' => ['sometimes', Rule::in(['active', 'suspended', 'banned'])],
            // Champs prestataire
            'metier' => 'sometimes|string',
            'bio' => 'sometimes|nullable|string',
            // Champs client
            'adresse' => 'sometimes|string',
            'telephone' => 'sometimes|nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['email', 'type', 'status']));

        // Mise à jour du profil spécifique
        if ($user->type === 'prestataire' && $user->prestataire) {
            $user->prestataire->update($request->only(['metier', 'bio']));
        } elseif ($user->type === 'client' && $user->client) {
            $user->client->update($request->only(['adresse', 'telephone']));
        }

        return response()->json($user->fresh()->load(['prestataire', 'client']));
    }

    // ==========================
    // SERVICES
    // ==========================
    public function listServices(Request $request)
    {
        $services = Service::with('prestataire')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($services);
    }

    public function moderateService(Service $service, Request $request)
    {
        $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'reason' => 'required_if:action,reject|nullable|string'
        ]);

        if ($request->action === 'approve') {
            $service->update(['status' => 'approved']);
            $message = 'Service approuvé avec succès';
        } else {
            $service->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason
            ]);
            $message = 'Service rejeté';
        }

        return response()->json(['message' => $message, 'service' => $service]);
    }

    // ==========================
    // STATISTIQUES & DASHBOARD
    // ==========================
    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'prestataires' => User::where('type', 'prestataire')->count(),
            'clients' => User::where('type', 'client')->count(),
            'services' => [
                'total' => Service::count(),
                'approved' => Service::where('status', 'approved')->count(),
                'pending' => Service::where('status', 'pending')->count()
            ],
            'recent_signups' => User::orderBy('created_at', 'desc')->limit(5)->get()
        ]);
    }

    public function dashboard()
    {
        return response()->json([
            'stats' => [
                'users' => User::count(),
                'prestataires' => User::where('type', 'prestataire')->count(),
                'clients' => User::where('type', 'client')->count()
            ]
        ]);
    }
    
}