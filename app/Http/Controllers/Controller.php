<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Mon API Blog",
 *     version="1.0.0",
 *     description="Documentation interactive de mon API de Blog"
 * )
 * @OA\Server(
 *     url="http://127.0.0",
 *     description="Serveur Local"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    // Laissez vide
}
