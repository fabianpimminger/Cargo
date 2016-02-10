<?php

namespace FabianPimminger\Cargo\Traits;

use FabianPimminger\Cargo\Attachment;
use FabianPimminger\Cargo\AttachmentUploader;
use FabianPimminger\Exceptions\AttachmentIsNotRegisteredException;

trait HasAttachmentTrait 
{
    
    protected $attachmentsToUpload = [];
    
    abstract public function registeredAttachments();
        
    public static function bootHasAttachmentTrait()
    {
        static::saved(function($instance){
            
            $instance->saveAttachments();
                    
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
                $this->attachmentsToUpload[$key] = new AttachmentUploader($key, $value, $this);;  
            }
            return;
        }
        parent::setAttribute($key, $value);
    }    
    
    public function saveAttachments()
    {
        
        $toUpload = $this->attachmentsToUpload;
        $this->attachmentsToUpload = [];
        
        foreach ($toUpload as $attachment) {
            
            $attachment->uploadFile();
            
        }
        
    }

}



