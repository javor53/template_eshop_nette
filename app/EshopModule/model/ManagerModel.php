<?php

namespace App\EshopModule\Model;

use Nette;

abstract class ManagerModel {
    
    use Nette\SmartObject;
    
    protected $database;
    
    function __construct(Nette\Database\Context $database) {
        
        $this->database = $database;
    
        
    }
}
