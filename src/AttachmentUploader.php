<?php

namespace FabianPimminger\Cargo;

use Intervention\Image\ImageManagerStatic as Image;
use FabianPimminger\Cargo\PathGenerators\PathGeneratorFactory;
use FabianPimminger\Cargo\Exceptions\FileNotSetException;
use FabianPimminger\Cargo\Exceptions\AttachmentContentNotRecognizedException;

class AttachmentUploader implements AttachmentInterface
{
    
    private $instance;
    private $name;
    private $file;
    private $originalFileName;
    private $config;
    
    function __construct($name, $file, $instance)
    {
        $this->name = $name;
        $this->instance = $instance;
        $this->config = $instance->getAttachmentConfig($name);
        
        $this->setFile($file);
    }   
    
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }
    
    public function getConfig()
    {
        return $this->config;
    }
    
    public function setFile($file)
    {
        
        if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            
            $this->file = $file;
            $this->setOriginalFileName($file->getClientOriginalName());
           return;    
        }
        
        if (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {
            
            $this->file = $file;            
            $this->setOriginalFileName(basename(parse_url($file, PHP_URL_PATH)));
            return;
        }
        
        throw new AttachmentContentNotRecognizedException();
                
    }
    
    public function getFile($file)
    {
        return $this->file;
    }
    
    public function setOriginalFileName($fileName)
    {
        $this->originalFileName = $this->sanitizeFileName($fileName);
    }
    
    public function getOriginalFileName()
    {
        return $this->originalFileName;
    }
    
    public function getOriginalFileNameWithoutExtension(){        
        return pathinfo($this->originalFileName, PATHINFO_FILENAME);
    }
    
    public function getFileName()
    {
        return $this->config["filename"]($this);
    }
    
    public function getFileExtension()
    {
        return ".".$this->config["format"];
    }
    
    public function uploadFile()
    {
                
        if(empty($this->file)) {
            throw new FileNotSetException();
        }
            
        $this->writeFiles();
        
        $this->setInstanceModelAttribute($this->config["fields"]["file_name"], $this->getFileName());
        $this->setInstanceModelAttribute($this->config["fields"]["updated_at"], date('Y-m-d H:i:s'));
        
        $this->saveInstanceModel();
                    
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
    
    private function writeFiles()
    {
        if(config("cargo.auto_orient")) {
            $image = Image::make($this->file)->orientate(); 
        }else {
            $image = Image::make($this->file);            
        }
                
        $this->writeOriginal($image);
                
        $this->writeStyles($image);
                
    }
    
    private function writeOriginal($image)
    {        
        $path = $this->getPath();
        
        $this->writeFileToStorage($path, $this->getFileName(), $image);
    }
    
    private function writeStyles($image)
    {
        $attachments = $this->instance->registeredAttachments();
        $image->backup("original");
        
        foreach ($attachments as $key => $attachment) {
            
            foreach ($attachment["styles"] as $styleName => $styleConverter){
                if(is_callable($styleConverter)) {
                    
                    $styleConverter($image);
                    
                    $path = $this->getPath($styleName);
                    
                    $this->writeFileToStorage($path, $this->getFileName(), $image);
                    
                    $image->reset("original");
                }
                
            }
            
        }        
    }
    
    private function writeFileToStorage($path, $filename, $image)
    {
        $imageFile = $image->encode($this->config["format"], $this->config["quality"]);
        
        \Storage::disk($this->config["disk"])->getDriver()->put($path.$filename, $imageFile->__toString(), ["ContentType" => $image->mime()]);      
    }
    
    private function is_closure($t) {
        return is_object($t) && ($t instanceof Closure);
    }
    
    private function getInstanceClassName()
    {
        return strtolower(str_replace("\\", "-", get_class($this->instance)));
    }
    
    public function getInstanceKey()
    {
        return $this->instance->getKey();
    }
    
    public function getPath($style = false)
    {
        $pathGenerator = PathGeneratorFactory::create();
        
        if($style != false) {
            return $pathGenerator->getPathForStyles($this).$style."/";
        } else {
            return $pathGenerator->getPath($this);  
        }
  
    }
    
    public function getFolder()
    {
        if(!empty($this->config["folder"])) {
            return $this->config["folder"];
        } else {
            return $this->getInstanceClassName();
        }  
    }
        
    public function sanitizeFileName($fileName)
    {

        $fileName = strtolower(trim($fileName));
    
        //replace accent characters, forien languages
        $search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'); 
        $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'); 
        $fileName = str_replace($search, $replace, $fileName);
    
        // remove - for spaces and union characters
        $find = array(' ', '&', '\r\n', '\n', '+', ',', '//');
        $fileName = str_replace($find, '-', $fileName);
    
        // Remove anything which isn't a word, whitespace, number
        // or any of the following caracters -_~,;[]().
        // If you don't need to handle multi-byte characters
        // you can use preg_replace rather than mb_ereg_replace
        // Thanks @Łukasz Rysiak!
        $fileName = mb_ereg_replace("([^a-zA-Z0-9\-_\.])", '', $fileName);
        // Remove any runs of periods (thanks falstro!)
        $fileName = mb_ereg_replace("([\.]{2,})", '', $fileName);
    
        return $fileName;
    }
}
    
    