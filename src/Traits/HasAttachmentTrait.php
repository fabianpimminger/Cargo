<?php

namespace FabianPimminger\Cargo\Traits;

use FabianPimminger\Cargo\Attachment;
use FabianPimminger\Cargo\AttachmentUploader;
use FabianPimminger\Cargo\AttachmentDeleter;
use FabianPimminger\Exceptions\AttachmentIsNotRegisteredException;

trait HasAttachmentTrait 
{
    
    protected $attachmentsToProcess = [];
    
    abstract public function registeredAttachments();
        
    public static function bootHasAttachmentTrait()
    {
        static::saved(function($instance)
        {
            
            $instance->processAttachments();
                    
        });
        
        
        static::deleting(function($instance) 
        {
            $instance->deleteAttachments();
        });
    }

    public function hasRegisteredAttachment($key)
    {
        return isset($this->registeredAttachments()[$key]);
    }
    
    public function getAttachmentConfig($key)
    {
        
        if(!$this->hasRegisteredAttachment($key)) {
            throw new AttachmentIsNotRegisteredException(); 
        }
        
        $default = [
            "filename" => function(AttachmentUploader $attachment){
                return $attachment->getOriginalFileNameWithoutExtension().$attachment->getFileExtension();
            },
            "missing_filename" => null,
            "folder" => null,
            "disk" => config("cargo.default_disk"),
            "styles" => [],
            "format" => config("cargo.default_format"),
            "quality" => config("cargo.default_quality"),
            "fields" => [
                "file_name" => "file_name",
                "updated_at" => "updated_at"
            ]
        ];
        
        $config = array_merge($default, $this->registeredAttachments()[$key]);
        
        if(!in_array($config["format"], ["jpg", "png"])){
            $config["format"] = "jpg";
        }
        
        return $config;    
    }

    public function getAttribute($key)
    {
        if ($this->hasRegisteredAttachment($key)) {
            return new Attachment($key, $this);
        }
        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if ($this->hasRegisteredAttachment($key)) {            
            if ($value) {
                $this->attachmentsToProcess[$key] = new AttachmentUploader($key, $value, $this);  
            }
            
            if (is_null($value)) {
               $this->attachmentsToProcess[$key] = new AttachmentDeleter($key, $this); 
            }
            return;
        }
        parent::setAttribute($key, $value);
    }    
    
    public function processAttachments()
    {
        
        $toProcess = $this->attachmentsToProcess;
        $this->attachmentsToProcess = [];
        
        foreach ($toProcess as $attachment) {
            
            $attachment->process();
            
        }
        
    }
    
    public function deleteAttachments()
    {
        foreach($this->registeredAttachments() as $key => $attachment){
            $this->attachmentsToProcess[$key] = new AttachmentDeleter($key, $this); 
        }
        
        $this->processAttachments();
    }

}



