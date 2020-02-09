<?php
namespace AdminModule;

use Nette;
use App\Model;
use App\Forms;
use Nette\Application\UI\Form;

class ContentPresenter extends BasePresenter
{
    /** @var Model\ContentModel @inject */
    public $contentModel;
    
    /** @var Forms\ContentForm @inject */
    public $contentForm;
    

    
    public function renderDefault() {
        $content = $this->contentModel->getContentType('defaultContent');
        $this->template->contact = $this->contentModel->getContentByType($content->id);
        
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
    
    
    
}
    