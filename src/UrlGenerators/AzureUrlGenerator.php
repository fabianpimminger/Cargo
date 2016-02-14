<?php
    
namespace FabianPimminger\Cargo\UrlGenerators;

class AzureUrlGenerator extends LocalUrlGenerator implements UrlGeneratorInterface
{
           
    public function getDomain()
    {
        return config('cargo.filesystem_config.azure.domain')."/";
    }

}