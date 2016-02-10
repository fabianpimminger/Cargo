<?php
    
namespace FabianPimminger\Cargo\UrlGenerators;

use FabianPimminger\Cargo\AttachmentInterface;
use FabianPimminger\Cargo\PathGenerators\PathGeneratorFactory;

class LocalUrlGenerator implements UrlGeneratorInterface
{
       
    public function getUrl(AttachmentInterface $attachment, $fileName, $style = false)
    {
        
        $filePath = $this->getBaseMediaDirectory().$this->getPath($attachment, $style).$fileName;
        
       
        return $this->getDomain().$filePath;
    }

    public function getPath(AttachmentInterface $attachment, $style = false)
    {
        $pathGenerator = PathGeneratorFactory::create();
        
        if($style != false) {
            return $pathGenerator->getPathForStyles($attachment).$style."/";
        } else {
            return $pathGenerator->getPath($attachment);  
        }
    }
    
    public function getDomain()
    {
        return url("/")."/";
    }

    protected function getBaseMediaDirectory()
    {
        $baseDirectory = str_replace(public_path()."/", "", $this->getStoragePath());
        return $baseDirectory."/";
    }
        
    public function getStoragePath()
    {
        $diskRootPath = config('filesystems.disks.media.root');
        
        return $diskRootPath;
    }

}