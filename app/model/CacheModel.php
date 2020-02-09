<?php
namespace App\Model;

use Nette;
use Nette\Caching\Cache;

class CacheModel extends ManagerModel
{
    use Nette\SmartObject;
    
    /**
     * @var Nette\Database\Context
     */
    protected $database;
    public $cache;
   

    public function __construct(Nette\Database\Context $database, Nette\Caching\IStorage $storage){
        
       // $this->database = $database;
        parent::__construct($database);
        
        //$storage = new \Nette\Caching\Storages\FileStorage($this->context->parameters['tempDir']);
        $cache = new \Nette\Caching\Cache($storage, 'example_data');
        $this->cache = $cache;
         
    }
    
    public function clearAll(){
        
        $this->cache->remove('events1');
        $this->cache->remove('events2');
        //$this->cache->clean([Cache::ALL]);
    }
    

    

    
}