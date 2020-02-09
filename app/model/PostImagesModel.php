<?php
namespace App\Model;

use Nette;
use App\Model;
use Nette\Utils\FileSystem;

/*
    database req
 *  name:post_images
 * id
 * dir
 * title
 * siffix
 * post_id
 *  */
//update 4.1.2019   added array export to "getPostImages"
//update 7.1.2019   added function "getNewPostImages"
class PostImagesModel extends ManagerModel
{

    public function saveImage($dir, $title, $suffix, $postId) {
        $id = $this->database->table('post_images')->insert([
            'dir' => $dir,
            'title' => $title,
            'suffix' => $suffix,
            'post_id' => $postId,
        ]);
        return $id;
    }
    
    public function getPostImages($arr){
        $list=[];
        if(!isset($arr)){
            return $this->database->table('post_images');
        }else{
            foreach ($this->database->table('posts')->where('id > ?', 0) as $item) {
            //parent_id povolit NULL a navázat přes FK na id
            //výběr kořenových prvků, případně doplnit access restrikci

            $list[$item->id]=$item->id; //počítá s UNIQUE title

            }
            return $list;
        }
    }
    public function getNewPostImages() {
        return $this->database->table('post_images')->where('post_id',NULL)->fetchAll();
    }
    public function getNewPostImagesList($postId = NULL) {
        $list=[];
        
        if(!isset($postId)){
            foreach ($this->database->table('post_images')->where('post_id',NULL) as $item) {
                $list[$item->id]=$item->id; //počítá s UNIQUE title
            }
        } else {
            foreach ($this->database->table('post_images')->where('post_id',$postId) as $item) {
                $list[$item->id]=$item->id; //počítá s UNIQUE title
            }
        }
            return $list;
    }
    
    public function getPostImage($id) {
        
        return $this->database->table('post_images')->where('id',$id)->fetch();
        
    }
    public function getImagesByPost($postId) {
        
        return $this->database->table('post_images')->where('post_id',$postId);
        
    }
    
    public function deletePostImage($id){
        $query = $this->database->table('post_images')->where('id', $id)->delete();
        return $query;
    }
    public function deleteAllPostImages($postId) {
        $images = $this->database->table('post_images')->where('post_id',$postId);
        foreach ($images as $img) {
           FileSystem::delete($this->getUrlPostImg($img->id)); 
        }
        
        
        $images = $this->database->table('post_images')->where('post_id',$postId)->delete();
        return $images;
        
    }
    public function getUrlPostImg($id){
     
        
        $dir = $this->database->query('SELECT dir FROM post_images WHERE id LIKE',$id)->fetch();
        $title = $this->database->query('SELECT title FROM post_images WHERE id LIKE',$id)->fetch();
        
        return "$dir->dir$title->title";
    }
    public function updatePostId($postId){
        $e = $this->database->query('UPDATE post_images SET post_id = ? WHERE post_id IS NULL',$postId);

         //$e = $this->database->table('post_images')->where('post_id',NULL)->update($postId);
         
        return $e;
    }
    

    
}