<?php
namespace App\Model;

use Nette;

/*
    database req
 *  name:posts_category
 * id
 * title
 * description
 *  */

class ArticleCategoryModel extends ManagerModel{
    
    use Nette\SmartObject;
    
    
    public function createCategory($title, $description) {
        
        $id = $this->database->table('posts_category')->insert([
            'title' => $title,
            'description' => $description,
        ]);
        return $id;
        
    }
    
    public function getCategories(){
        
        return $this->database->table('posts_category');
    }
    
    public function deleteCategory($categoryId){
            $query = $this->database->table('posts_category')->where('id', $categoryId)->delete();
            return $query;

    }
    
    public function getPostsByCategory($categoryId){
        return $this->database->table('posts')->where('post_category_id',$categoryId);
    }
    
    public function getCategoryById($categoryId){
        if($categoryId != NULL)
            $category = $this->database->query('SELECT * FROM posts_category WHERE id = ?', $categoryId )->fetch();
        else
        {
            $category = $this->database->query('SELECT * FROM posts_category ORDER BY id LIMIT 1')->fetch();
        }
        return $category;
    }
    
    public function getCategoryByTitle($title){
        $category = $this->database->query('SELECT * FROM posts_category WHERE title = ?', $title )->fetch();
        return $category;
    }
    
    public function isCategoryEmpty($idCol){
        if (!empty($this->database->query('SELECT * FROM posts WHERE post_category_id = ?', $idCol )
                ->fetchAll())){
            return false;
        }
        else{
            return true;
        }
    }
    
    public function editCategory($id, $title, $description){
        $this->database->query('
			UPDATE posts_category
			SET title = ?, description = ?
			WHERE id = ?
		', $title, $description, $id);
    }
    
    public function getCategoryTitles(){
        return $this->database->table('posts_category')->fetchPairs('id', 'title');
    }
    
    public function editPostCategory($id, $post_category_id){
        $this->database->query('
			UPDATE posts
			SET post_category_id = ?
			WHERE id = ?
		', $post_category_id, $id);
    }

}