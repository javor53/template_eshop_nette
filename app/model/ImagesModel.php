<?php
namespace App\Model;

use Nette;

/*
    database req
 * name:images
 * id
 * dir
 * tittle
 * suffix
 * collection_id
 *  */

class ImagesModel extends ManagerModel
{
    

    public function getByCollection($collection)
    {
        return $this->database->table('images')->where('collection_id', $collection)
                ->fetchAll();
    }
    
    public function getColl($id){
       
        try {
           $cl = $this->database->query('SELECT title FROM collections WHERE id > ?',$id)->fetch(); 
        } catch (Exception $exc) {
            
        }
        return $cl->title;    
    }
    public function getImg($id){

        return $this->database->table('images')->get($id);
     
    }
    public function ImgExists($title){
        //$this->database->table('images')->select('title')->where('title',$title);
        $exist = $this->database->query('SELECT title FROM images WHERE title =?',$title)->fetch();          
        if(!$exist){
            return false;
        }else{
            return true;
        }
            
    }
    
    public function getAll($orderType = NULL) {
        if($orderType == NULL){
            return $this->database->table('images');
        }
        elseif($orderType == "ALPHABETICAL"){
            return $this->database->query('SELECT * FROM images ORDER BY title')->fetchAll();
        }
        elseif($orderType == "COLLECTION"){
            return $this->database->query('SELECT * FROM images ORDER BY collection_id')->fetchAll();
        }
    }
    
    public function getUrlImg($id){
     
        
        $dir = $this->database->query('SELECT dir FROM images WHERE id LIKE',$id)->fetch();
        $title = $this->database->query('SELECT title FROM images WHERE id LIKE',$id)->fetch();
        $suf = $this->database->query('SELECT suffix FROM images WHERE id LIKE',$id)->fetch();
        return "$dir->dir$title->title$suf->suffix";
    }


    public function getList() {
        $list=[];

        foreach ($this->database->table('collections')->where('id > ?', 0) as $item) {
            //parent_id povolit NULL a navázat přes FK na id
            //výběr kořenových prvků, případně doplnit access restrikci

            $list[$item->id]=$item->title; //počítá s UNIQUE title

        }
        
        return $list;
    }
    
    public function insert($arrForm){
        
        $id = $this->database->table('images')->insert([
            'id' => $arrForm[0],
            'dir' => $arrForm[1],
            'title' => $arrForm[2],
            'suffix' => $arrForm[3],
            'collection_id' => $arrForm[4] 
        ]);
        return $id;
    }
    public function findAll($collection){
        return $this->database->table('images')->where('collection_id =?', $collection);
        
    }
    
    public function deleteImg($imgId) {
        $query = $this->database->table('images')->where('id', $imgId)->delete();
        
        if ($query !== 1) {
             //throw new \Exception();
        }
    }
    public function getDescByCollection($collection){
        $x = $this->database->table('images')->select('id')->where('collection_id',$collection);
        
        return $this->database->table('images_desc')->where('img_id',$x)->fetchAll();
        
    }
    public function getRandomImages($count){
        $images = $this->database->query('SELECT * FROM images ORDER BY RAND() LIMIT ?',$count)->fetchAll();
        return $images;
    }
    
    
}