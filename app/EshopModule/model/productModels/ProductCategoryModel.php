<?php

namespace App\EshopModule\Model\ProductModels;

use Nette;


class ProductCategoryModel extends \App\EshopModule\Model\ManagerModel{
    
    use Nette\SmartObject;
    
    /**
     * 
     * @return type
     * @throws \Exception
     */
    function getCategories(){
        $p = $this->database->query('SELECT * FROM product_category')->fetchAll();
        /*if(!$p){
            throw new \Exception('Product category not found');
        }*/
        return $p;
    }
    
    function getCategoriesArray(){
        $p = $this->getCategories();
        /*if(!$p){
            throw new \Exception('Product category not found');
        }*/
        foreach($p as $item){
            $arr[$item->id] = $item->title;
        }
        
        return $arr;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    function getCategory($id){
        $p = $this->database->query('SELECT * FROM product_category WHERE id=?',$id)->fetch();
        if(!$p){
            throw new \Exception('Product category not found');
        }
        return $p;
    }
    
    /**
     * 
     * @param type $title
     * @param type $desc
     */
    function createCategory($title,$desc){
        try {
            $this->database->table('product_category')->insert([
                    'title' => $title,
                    'description' => $desc
                 ]);
        } catch (\Exception $e) {

        }            
    }
    
    /**
     * 
     * @param type $id
     * @param type $title
     * @param type $desc
     */
    function updateCategory($id,$title,$desc){
        try {
            $this->database->table('product_category')->where('id',$id)->update([
                    'title' => $title,
                    'description' => $desc
                 ]);
        } catch (\Exception $e) {

        }
    }
    
    /**
     * 
     * @param type $id
     */
    function deleteCategory($id){
        try{
            $this->database->query('DELETE FROM product_category WHERE id=?',$id);
            return true;
        }
        catch(\Exception $e)
        {
            
        }
    }
    
}
