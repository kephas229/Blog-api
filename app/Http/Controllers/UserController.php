<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Retourne le profil de l'utilisateur connecté.
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Met à jour le profil de l'utilisateur connecté (nom, email, mot de passe).
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user'    => $user,
        ]);
    }

    /**
     * Liste tous les utilisateurs — réservé aux admins.
     */
    public function index(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Accès réservé aux administrateurs.'], 403);
        }

        $users = User::select('id', 'name', 'email', 'role', 'created_at')
            ->withCount('articles')
            ->latest()
            ->get();

        return response()->json($users);
    }

    /**
     * Change le rôle d'un utilisateur — réservé aux admins.
     * Un admin ne peut pas se rétrograder lui-même.
     */
    public function updateRole(Request $request, User $user)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Accès réservé aux administrateurs.'], 403);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas modifier votre propre rôle.'], 422);
        }

        $validated = $request->validate([
            'role' => 'required|in:author,admin',
        ]);

        $user->update(['role' => $validated['role']]);

        return response()->json([
            'message' => "Rôle de {$user->name} mis à jour : {$user->role}.",
            'user'    => $user,
        ]);
    }

    /**
     * Supprime un utilisateur — réservé aux admins.
     * Un admin ne peut pas se supprimer lui-même.
     */
    public function destroy(Request $request, User $user)
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Accès réservé aux administrateurs.'], 403);
        }

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 422);
        }

        $user->delete();

        return response()->json(['message' => "Utilisateur {$user->name} supprimé."]);
    }
}
