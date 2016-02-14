<?php

return [
    
    "default_disk" => "media",

    "default_format" => "jpg",

    "default_quality" => 80,

    "auto_orient" => true,
    
    "max_file_size" => 1024 * 3,

    "custom_url_generator_class" => null,
    
    "custom_path_generator_class" => null,    
    
    "filesystem_config" => [
        "azure" => [
            "domain" => ""
        ],
        "s3" => [
            "domain" => ""
        ]
    ],
];