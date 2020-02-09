<?php
namespace App\Model;

use Nette;

/*
    database table
 *  name: images_desc
 * id
 * title
 * description
 * img_id
 *  */


class ImageDescriptionModel extends ManagerModel
{
    public function insertImgDescription($arrDesc){
        $this->database->table('images_desc')->insert([
            'id' => $arrDesc[0],
            'title' => $arrDesc[1],
            'description' => $arrDesc[2],
            'img_id' => $arrDesc[3],
        ]);
        
    }

    public function getAllDescription(){
        
        return $this->database->table('images_desc');
    }

    public function getDescription($imgId){
        
        return $this->database->table('images_desc')->where('img_id',$imgId)->fetch();
    }
    
    public function deleteDesc($imgId) {
        $query = $this->database->table('images_desc')->where('id', $imgId)->delete();
        
        if ($query !== 1) {
             //throw new \Exception();
        }
    }
    
}