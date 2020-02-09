<?php
namespace AdminModule;

use Nette;
use App\Model;


use Nette\Application\UI\Form;
use Nette\Http\UrlScript;
use Nette\Http\IRequest;
use Nette\SmartObject;
use Nette\Utils\Image;
use Nette\Utils\FileSystem;
use Nette\Utils\Arrays;
use Nette\Utils\Html;


class PostPresenter extends BasePresenter
{
    /** @var Model\ArticleModel @inject */
    public $articleModel;
    
    /** @var Model\ImagesModel @inject */
    public $imagesModel;
    
    /** @var Model\ImageDescriptionModel @inject */
    public $imageDescModel;
    
    
    /** @var Model\PostImagesModel @inject */
    public $postImagesModel;
    
    /** @var Model\ArticleCategoryModel @inject */
    public $articleCategoryModel;
    
    /** @var Model\CacheModel @inject */
    public $cacheModel;
 
    
    private $database;
    
  

    public function __construct(Nette\Database\Context $database)
    {
        
        $this->database = $database;
      
    }
    public function renderDefault()
    {
        $this->template->posts = $this->database->table('posts')
                ->order('created_at DESC')
                ->limit(5);
    }
    public function renderAdminShow($page = 1) {
        
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $articlesCount = $this->articleModel->getPublicArticlesCount();
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($articlesCount); // celkový počet článků
        $paginator->setItemsPerPage(5); // počet položek na stránce
        $paginator->setPage($page); // číslo aktuální stránky
        
        $articles = $this->articleModel->getPublicArticles($paginator->getLength(), $paginator->getOffset());
        $previewImgs = $this->articleModel->getPreviewImgs();
        $publicated = $this->articleModel->getPublicatedPosts();
        $recommended = $this->articleModel->getRecommendedPosts();
        
        $this->template->publicated = $publicated;
        $this->template->recommended = $recommended;
        $this->template->placeholder = $this->articleModel->getPreviewImgUrl(NULL);
        $this->template->posts = $articles;
        $this->template->previewImgs = $previewImgs;
        $this->template->paginator = $paginator;
    }
    
    public function renderShow($postId)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            $this->error('Stranka nebyla nalezena');
        }
        $this->getHttpRequest()->getUrl()->getBasePath();
        $this->template->previewImg = $this->articleModel->getPreviewImgUrl($post->preview_img_id);
        $this->template->post = $post;

        $params = array('basePath' => $this->getHttpRequest()->getUrl()->basePath);
        $latte = new \Latte\Engine;
        $latte->setLoader(new \Latte\Loaders\StringLoader());
        $content = $latte->renderToString($post->content,$params);
        
        $this->template->content = $content;//strip_tags($content, '<b></b><img><i></i><br><a></a><ul></ul><ol></ol><li></li><u></u><font></font><span></span>') ;
                
        //$this->template->comments = $post->related('comment')->order('created_at');
          if ($this->isAjax()){
          $this->redrawControl('content');
        }  
    } 
    public function renderPostShow($postId){
        
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $images = $this->postImagesModel->getImagesByPost($postId);
        $this->template->images = $images; 
        if ($this->isAjax()){
          $this->redrawControl('content');
        }  
    }
    public function renderCreate($postId) {
        
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $newImages = $this->postImagesModel->getNewPostImages();
        $allImages = $this->postImagesModel->getPostImages(NULL);
        $this->template->newImages = $newImages;
        $this->template->allImages = $allImages;
        $this->template->post = $postId;
        
    }
    
    protected function createComponentCommentForm()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('name', 'Jméno:')
            ->setRequired();

        $form->addEmail('email', 'Email:');

        $form->addTextArea('content', 'Komentář:')
            ->setRequired();

        $form->addSubmit('send', 'Publikovat komentář');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];

        return $form;
    }
    
    public function commentFormSucceeded($form, $values)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        $postId = $this->getParameter('postId');

        $this->database->table('comments')->insert([
            'post_id' => $postId,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content,
        ]);

        $this->flashMessage('Děkuji za komentář', 'success');
        $this->redirect('this');
        }
         protected function createComponentPostForm()
        {
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('title', 'Titulek:')
            ->setRequired();
        $form->addTextArea('subtitle', 'Podtitulek:');

        $form->addText('author', 'Autor:');

        $form->addTextArea('content', 'Obsah:');
        

        $form->addSelect('preview_img_id', 'Náhledové foto:', $this->postImagesModel->getNewPostImagesList());   
        
        $form->addSelect('post_category_id')->setItems($this->articleCategoryModel->getCategoryTitles())->setDefaultValue(1);

        $form->addSubmit('send', 'Uložit a publikovat');

        $form->onSuccess[] = [$this, 'postFormSucceeded'];

        return $form;
    }
    
    public function postFormSucceeded($form, $values)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $postId = $this->getParameter('postId');
        if ($postId) {
            $post = $this->database->table('posts')->get($postId);
            $post->update($values);
        } else {
            $post = $this->database->table('posts')->insert($values);
            $this->database->table('posts')->where('id',$post)->update(['created_at' => new \DateTime()]);
        }
        
        if($this->postImagesModel->getNewPostImages() !== NULL){
            $this->postImagesModel->updatePostId($post->id);
        }
        $this->flashMessage("Příspěvek byl úspěšně vytvořen.", 'success');
        $this->redirect('this');//, $post->id
    }
    
    public function createComponentImgPostForm() {
        
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = new Form;
        $postsList = $this->articleModel->getPostsList();
        array_unshift($postsList, "--new--");
        
        
        $form->addUpload('file', 'Soubor:')
        ->setRequired();
        $form->addText('title', 'Název:')
        ->setRequired();
        $form->addSelect('menu', 'Přispěvek', $postsList)->setDefaultValue(0);
               
        $form->addSubmit('Upload');
        
        $form->onSuccess[] = [$this, 'imgPostFormSucceeded'];
        return $form;
    }

    public function imgPostFormSucceeded($form) {
        
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro nahrání obrázku se musíte přihlásit.');
        }

        $postId = $this->getParameter('postId');

        $values = $form->getValues();
        $file = $values['file'];
        // kontrola jestli se jedná o obrázek a jestli se nahrál dobře
        // 
        // oddělení přípony pro účel změnit název souboru na co chceš se zachováním přípony
        $file_ext = strtolower(mb_substr($file->getSanitizedName(), strrpos($file->getSanitizedName(), ".")));
        // vygenerování náhodného řetězce znaků, můžeš použít i \Nette\Strings::random()
        if($values->menu !== 0){
        
            $file_collection = $this->articleModel->getPostByTitle($values->menu);
        }else{
            $file_collection = NULL;
        }
        $file_name = $values->title . $file_ext;
        // přesunutí souboru z temp složky někam, kam nahráváš soubory
        $file->move('img/postsImg/' . $file_name);
        $path = "img/postsImg/";

        $img = Image::fromFile('img/postsImg/' . $file_name);
        //$img->resize(200, null);
        $img->save('img/postsImg/' . $file_name);

        $this->postImagesModel->saveImage($path,$file_name,$file_ext,$file_collection);

        

        $this->flashMessage('Obrazek byl úspěšně nahraný', 'success');
        $this->redirect('this');
    }
    public function createComponentEditImgPostForm() {
        
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = new Form;

        $postsList = $this->articleModel->getPostsList();
        array_unshift($postsList, "--new--");
        
        
        $form->addUpload('file', 'Soubor:')
        ->setRequired();
        $form->addText('title', 'Název:')
        ->setRequired();
        $form->addSelect('menu', 'Přispěvek', $postsList);
               
        $form->addSubmit('Upload');
        
        $form->onSuccess[] = [$this, 'editImgPostFormSucceeded'];
        return $form;
    }

    public function editImgPostFormSucceeded($form) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro nahrání obrázku se musíte přihlásit.');
        }
        $values = $form->getValues();
        $postId = $this->getParameter('postId');        

        $file = $values['file'];
        $file_ext = strtolower(mb_substr($file->getSanitizedName(), strrpos($file->getSanitizedName(), ".")));
        // vygenerování náhodného řetězce znaků, můžeš použít i \Nette\Strings::random()
        if($values->menu !== 0){
        
            $file_collection = $this->articleModel->getPostByTitle($values->menu);
        }else{
            $file_collection = NULL;
        }
        $file_name = $values->title . $file_ext;
        // přesunutí souboru z temp složky někam, kam nahráváš soubory
        $file->move('img/postsImg/' . $file_name);
        $dir = "img/postsImg/";

        $img = Image::fromFile('img/postsImg/' . $file_name);
        //$img->resize(200, null);
        $img->save('img/postsImg/' . $file_name);

        $this->postImagesModel->saveImage($dir,$file_name,$file_ext,$postId);

        $this->flashMessage('Obrazek byl úspěšně nahraný', 'success');
        $this->redirect('this');
    }
     
    public function createComponentMainPostForm() {
        
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = new Form;

        /*$data = array();
        foreach($this->articleModel->getPosts() as $r){
            $data[] = $r->toArray();//*on ActiveRow
        }
        //$form->addCheckboxList('mainCheck');
        $form->addCheckboxList('mainCheck',$data);*/
        
        $form->addSelect('mainPost', 'Výběr hlavního příspěvku' ,$this->articleModel->getPostsList());
        $form->addSubmit('Upload');
        
        $form->onSuccess[] = [$this, 'mainPostFormSucceeded'];
        return $form;
        
        
    }
    public function mainPostFormSucceeded($form) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro nahrání obrázku se musíte přihlásit.');
        }

        $id = $this->getParameter('postId');

        $values = $form->getValues();
        
        $mPost = $this->articleModel->getPostByTitle($values->mainPost);
        /*
        $items = $form->getHttpData($form::DATA_TEXT | $form::DATA_KEYS, 'mainCheck[]');
        
            $arrForm = [$id, \Nette\Utils\Arrays::get($items, 0)];*/
            
            $this->articleModel->saveMainPost([$id,$mPost]);
        

        
        $this->flashMessage('Vybraný článek byl zvolen jako hlavní', 'success');
        $this->redirect('this');
    }
    
    public function actionPublicate($postId)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        $this->articleModel->updatePublicatedTime($postId);
        $this->articleModel->setPublicPost($postId);
                
        $this->flashMessage('Příspěvek byl publikovaný', 'success');    
        $this->redirectUrl('admin-show');
    }
    
    
    public function actionSetRecommended($postId)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        $this->articleModel->setRecommendedPost($postId);
        $this->flashMessage('Příspěvek byl umístěný mezi doporučené', 'success');
        $this->redirect('Post:adminShow');
    }
    public function actionDeletePublicated($postId)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        $this->articleModel->deletePublicPost($postId);
        $this->flashMessage('Příspěvek byl skrytý', 'success');
        $this->redirect(':Admin:Post:adminShow');
    }
    public function actionDeleteRecommended($postId)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        $this->articleModel->deleteRecommendedPost($postId);
        $this->flashMessage('Příspěvek odebraný z doporučených', 'success');
        $this->redirect(':Admin:Post:adminShow');
    }

    public function actionEdit($postId)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        $post = $this->database->table('posts')->where('id',$postId)->fetch();
        $this->template->allImages = $allImages = $this->postImagesModel->getPostImages(NULL);
        $this->template->post = $postId;
        $this->template->content = $post->content;
        $this->template->imgsPost = $this->postImagesModel->getImagesByPost($postId);
        if (!$post) {
            $this->error('Příspěvek nebyl nalezen');
        }
        
        $this['postForm']->setDefaults(array(
            'id' => $post->id,
            'title' => $post->title,
            'subtitle' => $post->subtitle,
            'author' => $post->author,
            'content' => $post->content,
        ));
        $this['postForm']['preview_img_id']->setItems($this->postImagesModel->getNewPostImagesList($postId))->setDefaultValue($post->preview_img_id);
        $this['editImgPostForm']->setDefaults(array(
            'menu' => $post->title,
        ));
    }
    
    public function actionDeleteImg($id,$postId = NULL) {

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

            $post = $this->articleModel->getArticleById($postId);
        
        if($postId !== NULL){
            if($post->preview_img_id == $id){
                $this->flashMessage('Nelze smazat hlavní obrázek', 'fail');
                $this->redirect(':Admin:Post:edit', $post->id);
                return;
            }       
        }

        $file = $this->postImagesModel->getPostImage($id);

        FileSystem::delete($this->postImagesModel->getUrlPostImg($id));
        $this->postImagesModel->deletePostImage($id);

        $this->flashMessage('Obrazek byl úspěšně smazaný', 'success');
        if($post == NULL){
            $this->redirect(':Admin:Post:create', $postId);
        }
        $this->redirect(':Admin:Post:edit', $post->id);
    }
    
    public function actionDeletePost($id) {
       if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        
        $main = $this->articleModel->getMainPostId();
        if($main == $id){
            
            $this->flashMessage('Nelze smazat příspěvek označný jako hlavní', 'success');
            $this->redirect('Post:adminShow');
            return;
        }
        
        $this->cacheModel->clearAll();  //clean ALL CACHE !!!!!!
        $this->postImagesModel->deleteAllPostImages($id);
        $this->articleModel->deleteAllCommentsByPost($id);
        $this->articleModel->deletePost($id);
        $this->articleModel->deletePublicPost($id);
        $this->articleModel->deleteRecommendedPost($id);
        
        $this->flashMessage('Příspěvek byl úspěšně smazaný', 'success');
        $this->redirect('Post:adminShow'); 
        
    }
    

    
    
}