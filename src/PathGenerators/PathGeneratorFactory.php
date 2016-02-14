<?php
namespace FabianPimminger\Cargo\PathGenerators;
     
class PathGeneratorFactory
{
    public static function create()
    {
        $pathGeneratorClass = BasePathGenerator::class;
        $customPathClass = config('cargo.custom_path_generator_class');
        if ($customPathClass && class_exists($customPathClass) && class_implements($customPathClass, PathGeneratorInterface::class)) {
            $pathGeneratorClass = $customPathClass;
        }
        return app($pathGeneratorClass);
    }
}