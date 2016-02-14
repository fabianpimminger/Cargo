<?php

namespace FabianPimminger\Cargo;

use Intervention\Image\ImageManagerStatic as Image;
use FabianPimminger\Cargo\PathGenerators\PathGeneratorFactory;
use FabianPimminger\Cargo\Exceptions\FileNotExistsException;

class AttachmentDeleter implements AttachmentInterface, AttachmentProcessorInterface
{
    
    private $instance;
    private $name;
    private $file;
    private $originalFileName;
    private $config;
    
    function __construct($name, $instance)
    {
        $this->name = $name;
        $this->instance = $instance;
        $this->config = $instance->getAttachmentConfig($name);
    }   
    
    public function getConfig()
    {
        return $this->config;
    }
        
    public function process()
    {
        if (!empty($this->getInstanceModelAttribute($this->config["fields"]["file_name"]))) {
            $this->deleteFiles();
            
            $this->setInstanceModelAttribute($this->config["fields"]["file_name"], null);
            $this->setInstanceModelAttribute($this->config["fields"]["updated_at"], null);
            
            $this->saveInstanceModel();
        }                   
    }

    public function getFileName()
    {
        return $this->getInstanceModelAttribute($this->config["fields"]["file_name"]);
    }
    
    private function setInstanceModelAttribute($attribute, $value)
    {
        $fieldName = "{$this->name}_{$attribute}";
        $this->instance->setAttribute($fieldName, $value);
    }

    private function getInstanceModelAttribute($attribute)
    {
        $fieldName = "{$this->name}_{$attribute}";
        return $this->instance->getAttribute($fieldName);
    }
        
    private function saveInstanceModel()
    {
        $this->instance->save();
    }
    
    private function deleteFiles()
    {
        
        $path = $this->getPath();
                                        
        $this->deleteFromStorage($path);
                
    }
        
    private function deleteFromStorage($path)
    {     
        if (\Storage::disk($this->config["disk"])->getDriver()->has($path.$this->getFileName())) {
            \Storage::disk($this->config["disk"])->getDriver()->deleteDir($path);      
        } else {
            //maybe logging
        }
        
        
    }
        

    
    public function getInstanceKey()
    {
        return $this->instance->getKey();
    }
    
    public function getPath($style = false)
    {
        $pathGenerator = PathGeneratorFactory::create();

        return $pathGenerator->getPath($this);  
    }
    
    public function getFolder()
    {
        if(!empty($this->config["folder"])) {
            return $this->config["folder"];
        } else {
            return $this->getInstanceClassName();
        }  
    }
    
    private function getInstanceClassName()
    {
        return strtolower(str_replace("\\", "-", get_class($this->instance)));
    }
            
}
    
    