<?php

namespace App\Support;

use L5Swagger\CustomGeneratorInterface;
use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;
use OpenApi\Generator;

class SwaggerGeneratorFactory implements CustomGeneratorInterface
{
    public function create(): Generator
    {
        $oGenerator = new Generator();
        $oGenerator->setAnalyser(new ReflectionAnalyser([
            new AttributeAnnotationFactory(),
            new DocBlockAnnotationFactory(),
        ]));
        return $oGenerator;
    }
}
