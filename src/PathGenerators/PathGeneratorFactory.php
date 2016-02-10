<?php
namespace FabianPimminger\Cargo\PathGenerators;
     
class PathGeneratorFactory
{
    public static function create()
    {
        $pathGeneratorClass = BasePathGenerator::class;
        $customPathClass = config('cargo.custom_path_generator_class');
        if ($customPathClass && class_exists($customPathClass) && is_subclass_of($customPathClass, PathGenerator::class)) {
            $pathGeneratorClass = $customPathClass;
        }
        return app($pathGeneratorClass);
    }
}