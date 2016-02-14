<?php
    
namespace FabianPimminger\Cargo\UrlGenerators;

class AzureUrlGenerator extends LocalUrlGenerator implements UrlGeneratorInterface
{
           
    public function getDomain()
    {
        return config('cargo.azure.domain')."/";
    }

}