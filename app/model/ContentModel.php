<?php
namespace App\Model;

use Nette;
use Nette\Caching\Cache;


/*
    database req
 * name:content
 * id
 * type_id
 * title
 * description
 * 
 * content_type
 * id
 * title
 * 
 * editmode
 * id
 * state
 *  */

class ContentModel extends ManagerModel{
    
   
    public $editMode = 0;
       

    public function updateContent($type_id, $title, $description) {
        $id = $this->database->table('content')->where('type_id',$type_id)->update([
            'title' => $title,
            'description' => $description
        ]);
        return $id;
    }
    
    public function getContentByType($type_id) {
        return $this->database->table('content')->where('type_id',$type_id)->fetch();
    }
    
    public function getContentType($title){
        return $this->database->table('content_type')->where('title',$title)->fetch();
    }
    
    public function onEditMode(){
        
        
        if($this->editMode   == 0){
            $this->editMode  = 1;
        }

        return $this->editMode;
    }
    public function offEditMode(){
        if($this->editMode  == 1){
            $this->editMode  = 0;
        }
        return $this->editMode;
    }
}