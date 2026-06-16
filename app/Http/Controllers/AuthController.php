<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/register',
        summary: 'Créer un compte',
        description: 'Inscrit un nouvel utilisateur et retourne un token Bearer immédiatement utilisable.',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name',                  type: 'string',  example: 'Sophie Marchand',  description: 'Nom complet (max 255 caractères)'),
                    new OA\Property(property: 'email',                 type: 'string',  format: 'email', example: 'sophie@exemple.fr', description: 'Email unique'),
                    new OA\Property(property: 'password',              type: 'string',  format: 'password', example: 'motdepasse123', description: 'Minimum 8 caractères'),
                    new OA\Property(property: 'password_confirmation', type: 'string',  format: 'password', example: 'motdepasse123', description: 'Doit correspondre à password'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Compte créé avec succès',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message',      type: 'string',  example: 'Utilisateur créé avec succès !'),
                    new OA\Property(property: 'user',         ref: '#/components/schemas/User'),
                    new OA\Property(property: 'access_token', type: 'string',  example: '1|abc123xyz...'),
                    new OA\Property(property: 'token_type',   type: 'string',  example: 'Bearer'),
                ])
            ),
            new OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Utilisateur créé avec succès !',
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 201);
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Se connecter',
        description: 'Authentifie un utilisateur et retourne un token Bearer. Utilisez ce token dans l\'en-tête Authorization des routes protégées.',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email',    type: 'string', format: 'email',    example: 'admin@blogflow.fr'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Admin@2024!'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Connexion réussie',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message',      type: 'string', example: 'Connexion réussie !'),
                    new OA\Property(property: 'user',         ref: '#/components/schemas/User'),
                    new OA\Property(property: 'access_token', type: 'string', example: '2|def456uvw...'),
                    new OA\Property(property: 'token_type',   type: 'string', example: 'Bearer'),
                ])
            ),
            new OA\Response(response: 401, description: 'Identifiants incorrects', content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Identifiants incorrects.')])),
            new OA\Response(response: 422, description: 'Erreur de validation',    content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $user      = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Connexion réussie !',
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Se déconnecter',
        description: 'Révoque le token Bearer courant. Le token ne sera plus valide après cet appel.',
        tags: ['Authentification'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Déconnexion réussie',  content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Déconnexion réussie !')])),
            new OA\Response(response: 401, description: 'Non authentifié',      content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')])),
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie !']);
    }
}
