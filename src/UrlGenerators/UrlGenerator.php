<?php
    
namespace FabianPimminger\Cargo\UrlGenerators;

use FabianPimminger\Cargo\AttachmentInterface;
use FabianPimminger\Cargo\PathGenerators\PathGeneratorFactory;

abstract class UrlGenerator
{
    public function getPath(AttachmentInterface $attachment, $style = false)
    {
        $pathGenerator = PathGeneratorFactory::create();
        
        if($style != false) {
            return $pathGenerator->getPathForStyles($attachment).$style."/";
        } else {
            return $pathGenerator->getPath($attachment);  
        }
    }
}