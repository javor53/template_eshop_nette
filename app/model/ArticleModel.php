<?php
namespace App\Model;

use Nette;
use Nette\Utils\Paginator;

/*
    database req
 * name:posts
 * id
 * title
 * content
 * created_at
    
 * name:posts_main
 * id
 * post_id
 
 *name:comments 
 * id
 * content
 *  */

class ArticleModel extends ManagerModel{
    
    use Nette\SmartObject;
    
    public function createPaginator(){
        $paginator = new Nette\Utils\Paginator;
        return $paginator;
    }
            
    public function getPublicArticlesCount(){
        
        return $this->database->fetchField('SELECT COUNT(*) FROM posts WHERE created_at < ?', new \DateTime);          
    }
    public function getPublicArticlesIdArray($wh){
        
        $data = array();
        foreach($this->database->table('posts')->findAll($wh) as $r){
            $data[] = $r->toArray();//*on ActiveRow*/
        }
        $this->sendJson($data);
        return $data;
        
    }
    public function getPublicArticles($limit, $offset)
    {
        /*return $this->database->table('posts')
            ->where('created_at < ', new \DateTime, $limit, $offset)
            ->order('created_at DESC');
        */
        return $this->database->query('
            SELECT * FROM posts
            WHERE created_at < ?
            ORDER BY created_at DESC
            LIMIT ?
            OFFSET ?',
            new \DateTime, $limit, $offset
        );
    }
         
    
    public function getArticleById($postId){
        if ($postId == NULL){
            $post = $this->database->query(
                    'SELECT * FROM posts WHERE id = ( SELECT MAX(id) FROM posts )')
                    ->fetch();                
        } 
        
        return $this->database->table('posts')->get($postId);
    }
    
    public function getCommentToPost($post){
        /*if ($post == NULL){
            $post = $this->database->query(
                    'SELECT * FROM comments WHERE id = ( SELECT MAX(id) FROM comments )')
                    ->fetch();                
        } */
        return $post->related('comments')->order('created_at');
    }
    
    public function getPostsList() {
        $list=[];

        foreach ($this->database->table('posts')->where('id > ?', 0) as $item) {
            //parent_id povolit NULL a navázat přes FK na id
            //výběr kořenových prvků, případně doplnit access restrikci

            $list[$item->title]=$item->title; //počítá s UNIQUE title

        }
        
        return $list;
    }
    public function getPosts() {
        return $this->database->table('posts');
    }
    public function getPostByTitle($title){  
        return $this->database->table('posts')->select('id')->where('title',$title);
    }
    
    public function saveMainPost($arrForm) {
        
        $count = $this->database->table('posts_main')->count('id');
        if($count > 0){
           $this->database->table('posts_main')->select('id')->delete(); 
        }
        
        $id = $this->database->table('posts_main')->insert([
            'id' => $arrForm[0],
            'post_id' => $arrForm[1],
        ]);
        return $id;  
    }
    
    public function getMainPostId() {
        
        
        return $this->database->table('posts_main')->min('post_id');
    }
    
    public function getFirstById(){
        
        return $this->database->table('posts')->min('id');
    }
    public function deletePost($id) {
        
            
                
        $query = $this->database->table('posts')->where('id', $id)->delete();
    }
    public function deleteAllCommentsByPost($postId){
        $query = $this->database->table('comments')->where('post_id', $postId)->delete();
    }
    //12.01 
    public function getPreviewImgUrl($imgId){
        if($imgId == NULL){
            return "img/placeholder.png";
        }else
        {
            $img = $this->database->table('post_images')->where('id',$imgId)->fetch();
        }
        return "$img->dir$img->title";
        
    }
    //13.01
    public function getPostByImgId($id){
        $post = $this->database->table('posts')->where('preview_img_id',$id)->fetch();
        return $post;
    }
    public function getPreviewImgs(){       
            $imgs = $this->database->query(
                    'SELECT *
                    FROM `post_images`
                    WHERE `id` IN (SELECT preview_img_id
                    FROM `posts`)')
                    ->fetchAll();

        return $imgs;
    }
    //22.01 - publicated articles
    public function getPublicatedPosts(){ //returns posts_public table
        $posts =  $this->database->table('posts_public');
        if($posts->count('*') == 0){
            return NULL;
        }
        
        return $posts;
    }
    public function countPublicatedPosts() {
        $query = $this->database->fetchField('
            SELECT COUNT(*) FROM `posts`
                    WHERE 
                        `id` IN (SELECT post_id
                        FROM `posts_public`) 
                    AND 
                        created_at < ?
                        ORDER BY created_at DESC'
                ,new \DateTime
            );

        return $query;
    }
    
    public function getPublicatedPostsCount(){ //returns posts_public table
        $posts =  $this->database->table('posts_public');
        return $posts->count('*');
    }
    
    public function setPublicPost($postId){
        $id = $this->database->table('posts_public')->insert([
            'id' => NULL,
            'post_id' => $postId,
        ]);
       // $this->database->table('posts')->where('id',$postId)->update(['created_at' => new \DateTime()]);
        return $id;
    }
    public function updatePublicatedTime($postId){
        return $this->database->query('UPDATE posts SET', [
                'created_at' => new \DateTime(),
                ], 'WHERE id = ?', $postId);
        // $this->database->table('posts')->where('id',$postId)->update(['created_at' => new \DateTime()]);
    }
    
    
    public function deletePublicPost($postId){
        $posts =  $this->database->table('posts_public')->where('post_id',$postId)->delete();
        return $posts;
    }
    
    public function getPublicatedArticles($limit, $offset){
        return $this->database->query('
            SELECT *
                    FROM `posts`
                    WHERE 
                        `id` IN (SELECT post_id
                        FROM `posts_public`) 
                    AND 
                        created_at < ?
                        ORDER BY created_at DESC
                        LIMIT ?
                        OFFSET ?',
            new \DateTime, $limit, $offset
        );
    }
    public function getRecommendedPosts(){ //returns posts_public table
        $posts =  $this->database->table('posts_recommended');
        if($posts->count('*') == 0){
            return NULL;
        }
        
        return $posts;
    }
    public function setRecommendedPost($postId){
        $id = $this->database->table('posts_recommended')->insert([
            'id' => NULL,
            'post_id' => $postId,
        ]);
        return $id;
    }
    public function deleteRecommendedPost($postId){
        $posts =  $this->database->table('posts_recommended')->where('post_id',$postId)->delete();
        return $posts;
    }
    public function getRecommendedArticles($limit, $offset,$currentId){ //recommended except current post 
        return $this->database->query('
            SELECT *
                    FROM `posts`
                    WHERE 
                        `id` IN (SELECT post_id
                        FROM `posts_recommended`)
                    AND
                        `id` <> ?
                    AND 
                        created_at < ?
                        ORDER BY created_at DESC
                        LIMIT ?
                        OFFSET ?',
            $currentId, new \DateTime, $limit, $offset
        );
    }
    public function getPostsTitles(){
        return $this->database->query('SELECT id,title,post_category_id FROM posts');
    }
    
    public function sortTitle($orderType = NULL) {
        if($orderType == NULL){
            return $this->database->table('posts');
        }
        elseif($orderType == "ALPHABETICAL"){
            return $this->database->query('SELECT id,title,post_category_id  FROM posts ORDER BY title')->fetchAll();
        }
        elseif($orderType == "CATEGORY"){
            return $this->database->query(
                    'SELECT id,title,post_category_id
                            FROM posts 
                            ORDER BY post_category_id'
                    )->fetchAll();
            
        }
    }
    
    public function sortCategory($orderType = NULL) {
        if($orderType == NULL){
            return $this->database->table('posts_category');
        }
        elseif($orderType == "ALPHABETICAL"){
            return $this->database->table('posts_category');
        }
    }
}
