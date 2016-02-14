<?php
    
namespace FabianPimminger\Cargo;

use FabianPimminger\Cargo\Attachment;

interface AttachmentInterface
{
       
    public function getInstanceKey();

    public function getFolder();    
    
    public function getFileName();
    
}    