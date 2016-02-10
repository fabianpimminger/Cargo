<?php
    
namespace FabianPimminger\Cargo\PathGenerators;

use FabianPimminger\Cargo\AttachmentInterface;

class BasePathGenerator implements PathGeneratorInterface
{
       
    public function getPath(AttachmentInterface $attachment)
    {
        return $this->getFolder($attachment)."/".$this->getUniqueBasePath($attachment)."/";
    }

    public function getPathForStyles(AttachmentInterface $attachment)
    {
        return $this->getFolder($attachment)."/".$this->getUniqueBasePath($attachment)."/s/";
    }
    
    public function getUniqueBasePath(AttachmentInterface $attachment)
    {
        return $attachment->getInstanceKey();
    }
    
    public function getFolder(AttachmentInterface $attachment)
    {
        return $attachment->getFolder();
    }

}