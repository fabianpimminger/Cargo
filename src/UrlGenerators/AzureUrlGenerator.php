<?php
    
namespace FabianPimminger\Cargo\UrlGenerators;

use FabianPimminger\Cargo\AttachmentInterface;

class AzureUrlGenerator extends UrlGenerator implements UrlGeneratorInterface
{

    public function getUrl(AttachmentInterface $attachment, $fileName, $style = false)
    {          
        return $this->getDomain().$this->getStorageContainer().$this->getPath($attachment, $style).$fileName;
    }
    
    public function getDomain()
    {
        return config('cargo.filesystem_config.azure.domain')."/";
    }
        
    public function getStorageContainer()
    {
        $diskRootPath = config('cargo.filesystem_config.azure.container')."/";
        
        return $diskRootPath;
    }
              


}