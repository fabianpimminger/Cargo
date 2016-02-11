<?php

namespace FabianPimminger\Cargo;

use Intervention\Image\ImageManagerStatic as Image;
use FabianPimminger\Cargo\PathGenerators\PathGeneratorFactory;
use FabianPimminger\Cargo\UrlGenerators\UrlGeneratorFactory;
use FabianPimminger\Cargo\Exceptions\FileNotSetException;
use FabianPimminger\Cargo\Exceptions\AttachmentContentNotRecognizedException;
use FabianPimminger\Cargo\Exceptions\AttachmentNotExistsException;

class Attachment implements AttachmentInterface
{
    
    private $instance;
    private $name;
    private $fileName;
    private $config;
    
    function __construct($name, $instance)
    {
        $this->name = $name;
        $this->instance = $instance;
        $this->config = $instance->getAttachmentConfig($name);
        
        $this->fileName = $this->getInstanceModelAttribute($this->config["fields"]["file_name"]);
                
    }   
    
    public function getConfig()
    {
        return $this->config;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getInstanceKey()
    {
        return $this->instance->getKey();
    }
    
    public function getFolder()
    {
        if(!empty($this->config["folder"])) {
            return $this->config["folder"];
        } else {
            return $this->getInstanceClassName();
        }  
    }

    public function url($style = false){
                
        if (!empty($this->fileName)) {
            $urlGenerator = UrlGeneratorFactory::create();
                    
            return $urlGenerator->getUrl($this, $this->fileName, $style);
        }
        
        return $this->getMissingUrl();
    }

    public function getMissingUrl()
    {
        
        if(empty($this->config["missing_filename"])){
            return null;
        }else{            
            $urlGenerator = UrlGeneratorFactory::create();
            
            return $urlGenerator->getUrl($this, $this->config["missing_filename"], true);            
        }
    }        
            
    private function getInstanceModelAttribute($attribute)
    {
        $fieldName = "{$this->name}_{$attribute}";
        return $this->instance->getAttribute($fieldName);
    }
    
    private function getInstanceClassName()
    {
        return strtolower(str_replace("\\", "-", get_class($this->instance)));
    }
}
    
    