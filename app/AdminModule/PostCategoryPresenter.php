<?php
namespace AdminModule;

use Nette;
use App\Model;
use App\Forms;
use Nette\Application\UI\Form;


class PostCategoryPresenter extends BasePresenter{
    /** @var Model\ArticleModel @inject */
    public $articleModel;
    
    /** @var Model\ArticleCategoryModel @inject */
    public $articleCategoryModel;
    
    /** @var Forms\CategoryForm @inject */
    public $categoryForm;
    
    
    public function handleSort($type){
        $articles = $this->articleModel->sortTitle($type);
       //$categories = $this->articleModel->sortCategory($type);
        $this->template->articles = $articles;
       // $this->template->categories = $categories;
        if ($this->isAjax()) {
            $this->redrawControl('sortArticles');
        }else {
            $this->redirect('this');
        }
    }
    
    
    
    public function renderDefault() {
        
    }
    public function renderCreate(){
        
    }
    public function renderAdminShow(){
        if(!isset($this->template->articles)){
            $this->template->articles = $this->articleModel->getPostsTitles();      
        }
        $this->template->categories = $this->articleCategoryModel->getCategories();
            
    }



    public function createComponentCategoryForm(){
        
        $form = $this->categoryForm->create();
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $this->articleCategoryModel->createCategory(
                        $values->title,
                        $values->description
                        );
                $this->redirect(':Admin:PostCategory:adminShow');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
    public function createComponentEditCategoryForm(){
        
        $form = $this->categoryForm->createEditForm();
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $this->articleCategoryModel->editCategory(
                        $values->id,
                        $values->title,
                        $values->description
                        );
                $this->redirect(':Admin:PostCategory:adminShow');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
    public function createComponentPostCategoryEditForm(){
        
        $form = $this->categoryForm->createPostCategoryEditForm();
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $category = $this->articleCategoryModel->getCategoryByTitle($values->category);
                $this->articleCategoryModel->editPostCategory(
                        $values->id,
                        $category->id
                        );
                $this->redirect(':Admin:PostCategory:adminShow');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
    
    public function actionDelete($id) {

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        if(!$this->articleCategoryModel->isCategoryEmpty($id)){
            $this->flashMessage('Nelze smazat kategorie musí být prázdná', 'success');
            $this->redirect('adminShow');
        }
        
        $this->articleCategoryModel->deleteCategory($id);
        $this->flashMessage('Kategorie byla smazána', 'success');
        $this->redirect('adminShow');
    }
    public function actionEdit($id) {

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        $category = $this->articleCategoryModel->getCategoryById($id);
         

        if (!$category) {
            $this->error('Příspěvek nebyl nalezen');
        }
        
        $this['editCategoryForm']->setDefaults(array(
            'id' => $category->id,
            'title' => $category->title,
            'description' => $category->description,
        ));
    }
    public function actionPostCategoryEdit($id) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        $post = $this->articleModel->getArticleById($id);
            
        $this->template->post = $post;
        if (!$post) {
            $this->error('Příspěvek nebyl nalezen');
        }
        $category = $this->articleCategoryModel->getCategoryById($post->post_category_id);
        
        $this['postCategoryEditForm']->setDefaults(array(
            'id' => $post->id,
            'category' => $category->title,
        ));
    }
}