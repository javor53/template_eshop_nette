<?php
  
namespace AdminModule;

use Nette;
use App\Model;
use Nette\Application\UI\Form;

class CollectionsPresenter extends BasePresenter
{
    /** @var Model\CollectionsModel @inject */
    public $collectionsModel;
   

    public function renderDefault() {
          $this->template->collections = $this->collectionsModel->getCollections();
         
    }
    public function renderAdminShow() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
          $this->template->collections = $this->collectionsModel->getCollections();
         
    }
    
    public function createComponentCollectionForm() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = new Form;
        $form->addText('title', 'Název:')
        ->setRequired(); 
        $form->addText('content', 'Popis:');
        
        $form->addSubmit('send');
        
        $form->onSuccess[] = [$this, 'collectionFormSucceeded'];
        return $form;
    }
    
    public function collectionFormSucceeded($form, $values)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }    
        $postId = $this->getParameter('postId');

        $this->collectionsModel->insert($values->title, $values->content);



        $this->flashMessage('Kategorie byla vytvořena', 'success');
        $this->redirect('adminShow');
    }
    
    public function actionDelete($id) {

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        if(!$this->collectionsModel->isCollectionEmpty($id)){
            $this->flashMessage('Nelze smazat kategorie musí být prázdná', 'success');
            $this->redirect('adminShow');
        }
        
        $this->collectionsModel->deleteByID($id);
        $this->flashMessage('Kategorie byla smazána', 'success');
        $this->redirect('adminShow');
    }
    
}