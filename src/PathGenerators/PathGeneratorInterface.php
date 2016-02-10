<?php
    
namespace FabianPimminger\Cargo\PathGenerators;

use FabianPimminger\Cargo\AttachmentInterface;

interface PathGeneratorInterface
{
       
    public function getPath(AttachmentInterface $attachment);

    public function getPathForStyles(AttachmentInterface $attachment);    

}