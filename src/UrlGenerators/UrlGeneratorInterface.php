<?php
    
namespace FabianPimminger\Cargo\UrlGenerators;

use FabianPimminger\Cargo\AttachmentInterface;

interface UrlGeneratorInterface
{
       
    public function getUrl(AttachmentInterface $attachment, $fileName, $style = false);


}