<?php

namespace EshopModule;

use Nette;
use App\EshopModule\Model;
use Nette\Application\UI\Form;
use App\EshopModule\Components\Forms;

final class OrderEmailsPresenter extends BasePresenter
{
    
    /** @var Model\OrderManagerModel @inject */
    public $orderManagerModel;
    
    /** @var Model\OrderEmailModel @inject */
    public $orderEmailModel;
    
    /** @var Model\ShippingInfoModel @inject */
    public $shippingInfoModel;
    
    /** @var Forms\OrderForm @inject */
    public $orderForm;
    
    
    public function handleSaveEmailSending(){
        $emails = $this->orderEmailModel->getEmails();
        $this->template->emails = $emails;        
        
        if ($this->isAjax()) {
            $this->redrawControl('emails');
        }else {
            $this->redirect('this');
        }
    }
    
    public function renderDefault($id){
        
        
    } 
    
    public function renderSettings(){
        $emails = $this->orderEmailModel->getEmails();
        $this->template->configType = $this->shippingInfoModel->configStatus;
        $this->template->emails = $emails;
        $accSetting = $this->orderEmailModel->getEmailSetting();
        
        $i = 0;
        foreach ($emails as $email) {
            $this["emailTypeMultiplierForm"]['email'][$i]->setDefaults(array('sending' => $email->sending)); 
            $i++;
        }
        
        $this['editEmailSettingForm']->setDefaults([
            'id' => $accSetting->id,
            'host' => $accSetting->host,
            'username' => $accSetting->username,
            'password' => $accSetting->password,
            'secure' => $accSetting->secure,
            'name' => $accSetting->name,
                ]);
    } 
    
    public function renderDetail($id){
        $email = $this->orderEmailModel->getEmail($id);

        $this->template->email = $email;
        
        $this['editEmailForm']->setDefaults([
            'id' => $email->id,
            'subject' => $email->subject,
            'type' => $email->type,
            'content' => $email->content
                ]);
    } 
    
    public function createComponentAddEmailForm($id) {
        
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = $this->orderForm->createEmail();
            
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {   
                $this->orderEmailModel->createEmail(
                        $values->subject,
                        $values->content,
                        $values->type
                        );
                $this->flashMessage('E-mail byl vytvořen.', 'success');
                $this->redirect('OrderEmails:settings');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };
        return $form;
    }
    
    public function createComponentEditEmailForm($id) {
        
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = $this->orderForm->createEditEmail();
            
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {   
                $this->orderEmailModel->updateEmail(
                        $values->id,
                        $values->subject,
                        $values->content,
                        $values->type
                        );
                $this->flashMessage('E-mail byl vytvořen.', 'success');
                $this->redirect('this');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };
        return $form;
    }
    
    public function createComponentEditEmailSettingForm($id) {
        
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = $this->orderForm->createEditEmailSetting();
            
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {   
                $this->orderEmailModel->updateEmailSetting(
                        $values->id,
                        $values->host,
                        $values->username,
                        $values->password,
                        $values->secure,
                        $values->name
                        );
                $this->flashMessage('Nastavení e-mailového účtu bylo uloženo.', 'success');
                $this->redirect('OrderEmails:settings');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };
        return $form;
    }
    
    public function createComponentEmailTypeMultiplierForm() {
        
        $form = new Nette\Application\UI\Form;

        $numOfEmails = $this->orderEmailModel->getEmailCount();
                
        $form->addMultiplier('email', function (Nette\Forms\Container $container, Nette\Forms\Form $form) {
            $container->addCheckbox('sending')
                    ->setAttribute('class', 'form-control form-checkbox');
            $container->addHidden('ids');
        },$numOfEmails);

        $form->addSubmit('submit', 'uložit');
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $values = $form->getValues();
                $i=0;
                foreach ($values['email'] as $value) {   
                    $sends[$i] = $value['sending'];
                    $ids[$i] = $value['ids'];
                    $i++;
                }

                $this->orderEmailModel->updateEmailSending($ids, $sends);
                
                $this->flashMessage('Změny byly uloženy.', 'success');
                $this->handleSaveEmailSending();    
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };   
        
        return $form;
    }

    public function actionDeleteEmail($id){

        $p = $this->orderEmailModel->deleteEmail($id);
        if($p == -1){
            $this->flashMessage('Nelze smazat.', 'fail');
            $this->redirect('OrderEmails:settings');
        }
        $this->flashMessage('E-mail byl smazaný.', 'success');
        $this->redirect('OrderEmails:settings');
    }
    
}
