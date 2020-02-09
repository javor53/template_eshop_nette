<?php

namespace PublicModule;

use Nette;
use App\Model;
use App\Forms;
use Nette\Application\UI\Form;
use Nette\Http\SessionSection;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    /** @var Model\ImagesModel @inject */
    public $imagesModel;
    
    /** @var Model\CollectionsModel @inject */
    public $collectionsModel;
    
    /** @var Model\UserManager @inject */
    public $userManager;
    
    /** @var Model\ContentModel @inject */
    public $contentModel;
    
    /** @var Forms\ContentForm @inject */
    public $contentForm;
    
    /** @var \App\EshopModule\Components\Forms\OrderForm @inject */
    public $orderForm;
    
    
    /** @var \App\EshopModule\Model\ProductManagerModel @inject */
    public $productManagerModel;
    
    /** @var \App\EshopModule\Model\CustomerModel @inject */
    public $customerModel;
     
    /** @var \App\EshopModule\Model\CartModel @inject */
    public $cartModel;
    
    protected $admin;
     
    
    /** @var Nette\Http\Session */
    private $session;

    /** @var Nette\Http\SessionSection */
    public $sessionSection;

    public function __construct(Nette\Http\Session $session)
    {
        $this->session = $session;

        // a získáme přístup do sekce 'mySection':
        $this->sessionSection = $session->getSection('editModeSection');
        
    }
    
    
    
    public function startup() {
        parent::startup();
        $this->template->admin = $this->userManager->getByID($this->user->getId());
        $this->sessionSection->setExpiration('20 minutes');
       //$this->sessionSection->editMode = $this->contentModel->editMode;
    }
    
    public function handleOnEditMode(){
        
        $this->template->editMode = $this->contentModel->onEditMode();
        
        $this->sessionSection->editMode = 1;
        
        if ($this->presenter->isAjax()) {
            $this->redrawControl('editMode');
            $this->redrawControl('editModeButton');
        }
        
        $this->flashMessage('Editační režim zapnutý', 'success');
    }
    
    public function handleOffEditMode(){
        
        $this->template->editMode = $this->contentModel->offEditMode();
        
       $this->sessionSection->editMode = 0;
        
        if ($this->presenter->isAjax()) {
            $this->redrawControl('editMode');
            $this->redrawControl('editModeButton');
        }
        
        $this->flashMessage('Editační režim vypnutý', 'success');
    }
    
    public function handleAddToCart($id) {
        
        $this->cartModel->add($id, 1);
        $this->template->cart = $this->cartModel->getCart();
        
        if ($this->isAjax()) {
            $this->redrawControl('cart');
        }else {
            $this->redirect('this');
        }
    }
    
    public function handleRemoveFromCart($id) {

        $this->cartModel->remove($id);
        $this->template->cart = $this->cartModel->getCart();
        
        if ($this->isAjax()) {
            $this->redrawControl('cart');
        }else {
            $this->redirect('this');
        }
    }
    
    public function handleRecount() {
        $this->template->cart = $this->cartModel->getCart();
        
        if ($this->isAjax()) {
            $this->redrawControl('cart');
        }else {
            $this->redirect('this');
        }
    }




    public function renderDefault($id){
        $collections = $this->collectionsModel->getCollections();
        $images = $this->imagesModel->getRandomImages(9);
        $products = $this->productManagerModel->getProducts();
        
        if(!isset($this->template->cart))
            $this->template->cart = $this->cartModel->getCart();
        if(!isset($this->template->editMode))
            $this->template->editMode = $this->sessionSection->editMode;
        
       
        $this->template->products = $products;
        $this->template->collections = $collections;
        
        $this->template->images = $images;
        
        $content = $this->contentModel->getContentType('defaultContent');
        $this->template->contact = $this->contentModel->getContentByType($content->id);
        
    }
    public function renderGallery($galleryId) {
        if($galleryId == NULL){
            $collection = $this->imagesModel->getColl(1);//select default collection
        }
        
        $collection = $this->collectionsModel->getById($galleryId);
        $images = $this->imagesModel->getByCollection($galleryId);
        
 
        $this->template->collection = $collection;
        $this->template->images = $images;
    }
    public function renderContact($id){
        $images = $this->imagesModel->getRandomImages(9);
        
        $this->template->images = $images;
    }
    
    public function createComponentDefaultForm() {
        $form = $this->contentForm->createContentForm();
        
        $content = $this->contentModel->getContentType('defaultContent');
        $about = $this->contentModel->getContentByType($content->id);
        
        
        $form->setDefaults([
            'id' => $about->id,
            'type_id' => $content->id,
            'title' => $about->title,
            'description' => $about->description
        ]);
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $content = $this->contentModel->getContentType('defaultContent');
                $this->contentModel->updateContent(
                        $content->id,
                        $values->title,
                        $values->description
                        );
                
                $this->flashMessage('Změna proběhla úspěšně', 'success');
                $this->redirect('this');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
    
    public function createComponentRecount() {
        $form = new Form;
        $form->addProtection('Platnost formuláře vypršela! Zkuste to prosím znovu.');
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'recountSucceeded'];
        return $form;
    }
    
    public function recountSucceeded(Form $form) {
        $cnt = $form->getHttpData($form::DATA_TEXT, 'cnt[]');
            
        $this->cartModel->recount($cnt);
        
        $this->handleRecount();
    }
    
    public function createComponentOrderForm() {
        $form = $this->orderForm->create();
        
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                
                $id = $this->customerModel->purchase($values);
                //$this->error($id);
                
                $this->redirect(':Public:Homepage:default');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
    
    
    
    public function actionChangeEditMode(){
        
        $this->redirect(':Public:Homepage:');
    }
            
    
}
