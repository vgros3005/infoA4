<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Gestion Fiches A4 - API',
    version: '1.0.0',
    description: 'API REST pour la gestion et le suivi des fiches A4 (demandes de développement DSI)',
    contact: new OA\Contact(email: 'admin@infoa4.local'),
    license: new OA\License(name: 'Propriétaire')
)]
#[OA\Server(url: '/api', description: 'API principale')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Tag(name: 'Fiches A4', description: 'Gestion des demandes A4')]
#[OA\Tag(name: 'Tâches', description: 'Gestion des tâches et Gantt')]
#[OA\Tag(name: 'Priorités', description: 'Paramétrage des priorités')]
class OpenApiSpec
{
    // Classe dédiée aux annotations OpenAPI globales
}
