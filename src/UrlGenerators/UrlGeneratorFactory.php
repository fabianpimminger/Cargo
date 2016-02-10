<?php
namespace FabianPimminger\Cargo\UrlGenerators;
     
class UrlGeneratorFactory
{
    public static function create()
    {
        $urlGeneratorClass = LocalUrlGenerator::class;
        $customUrlClass = config('cargo.custom_url_generator_class');
        if ($customUrlClass && class_exists($customUrlClass) && is_subclass_of($customUrlClass, UrlGenerator::class)) {
            $urlGeneratorClass = $customUrlClass;
        }
        return app($urlGeneratorClass);
    }
}