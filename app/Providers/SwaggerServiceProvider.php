<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;

class SwaggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Injecte l'analyser combiné (Attributes + DocBlocks) dans la config runtime,
        // avant que L5-Swagger crée son Generator. Pas compatible avec config:cache,
        // mais correct en développement.
        config([
            'l5-swagger.defaults.scanOptions.analyser' => new ReflectionAnalyser([
                new AttributeAnnotationFactory(),
                new DocBlockAnnotationFactory(),
            ]),
        ]);
    }
}
