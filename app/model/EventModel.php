<?php
namespace App\Model;

use Nette;

/*
    database req
 * name:events
 * id
 * title
 * description
 * date
 * place
 * image_id
    
 * name:events_images
 * id
 * dir
 * title
 * suffix
 
 *  */

class EventModel extends ManagerModel{
    
    public function createEvent($title, $description, $date, $place, $imgId) {
        $id = $this->database->table('events')->insert([
            'title' => $title,
            'description' => $description,
            'date' => $date,
            'place' => $place,
            'image_id' => $imgId,
        ]);
        return $id;
    }
    
    public function uploadImage($dir, $title, $suffix) {
        $id = $this->database->table('events_images')->insert([
            'dir' => $dir,
            'title' => $title,
            'suffix' => $suffix,
        ]);
        return $id;
    }
    
    public function getEvents() {
        return $this->database->table('events');
    }
    
    public function getImages(){
        return $this->database->table('events_images');
    }
    
    public function getEventById($eventId){
        return $this->database->table('events')->where('id',$eventId)->fetch();
    }
    
    public function getImageById($imgId){
        return $this->database->table('events_images')->where('id',$imgId)->fetch();
    }
    
    public function editEvent($id, $title, $description, $date, $place) {
        $this->database->query('
			UPDATE events
			SET title = ?, description = ?, date = ?, place = ? 
			WHERE id = ?
		', $title, $description, $date, $place, $id);
    }
    public function imgExists($title){
        $exist = $this->database->query('SELECT title FROM events_images WHERE title =?',$title)->fetch();          
        if(!$exist){
            return false;
        }else{
            return true;
        }    
    }
    public function getUrlImg($id){
        $dir = $this->database->query('SELECT dir FROM events_images WHERE id =',$id)->fetch();
        $title = $this->database->query('SELECT title FROM events_images WHERE id =',$id)->fetch();
        $suf = $this->database->query('SELECT suffix FROM events_images WHERE id =',$id)->fetch();
        return "$dir->dir$title->title$suf->suffix";
    }
    public function deleteImg($imgId) {
        $query = $this->database->table('events_images')->where('id', $imgId)->delete();        
        if ($query !== 1) {
             //throw new \Exception();
        }
    }
    public function deleteEvent($eventId) {
        $query = $this->database->table('events')->where('id', $eventId)->delete();        
        return $query;
    }
    
    
}