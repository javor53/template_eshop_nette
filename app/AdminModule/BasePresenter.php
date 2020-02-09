<?php

namespace AdminModule;

use Nette;
use App\Model;
use App\Forms;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
    
    /** @var Model\LoginFailModel @inject */
    public $loginFailModel;
    
    /** @var Model\UserManager @inject */
    public $userManager;
    
    /** @var Model\ContentModel @inject */
    public $contentModel;
    
    /** @var Forms\UserForm @inject */
    public $userForm;
    
    
    protected $admin;
    
    public function beforeRender(){
        parent::beforeRender();
        $this->template->admin = $this->admin;
        $this->template->menuItems = [
            'Homepage' => ['Hlavní stránka' => ':Public:Homepage:default'],    
            //'Moduly' => ['Blog' => ':Admin:Homepage:default', 'Eshop' => ':Eshop:Homepage:default'],
            'Galerie' => ['Správa obrázků' => ':Admin:Images:upload','Správa kategorií' => ':Admin:Collections:adminShow'],
            'Blog' => ['Seznam článků' => ':Admin:Post:adminShow'  , 'Rubriky' => ':Admin:PostCategory:adminShow','Vytvořit nový článek' => ':Admin:Post:create'],
            'Události' => ['Seznam událostí' => ':Admin:Event:adminShow'  , 'Vytvořit událost' => ':Admin:Event:create'],
            'Nastavení' => ['Správa uživatelů' => ':Admin:AdminUser:userTable'],
        ]; 
        $this->template->menuItemsJournalist = [
            'Homepage' => ['Hlavní stránka' => ':Public:Homepage:default'],
            'Galerie' => ['Správa obrázků' => ':Admin:Images:upload','Správa kategorií' => ':Admin:Collections:adminShow'],
            'Blog' => ['Seznam článků' => ':Admin:Post:adminShow'  ,'Vytvořit nový článek' => ':Admin:Post:create'],
        ];


    }
    public function startup() {
        parent::startup();
        $this->admin = $this->userManager->getByID($this->user->getId());
    }
    
    public function render(array $data = [])

    {

        foreach ($data as $key => $value)

            $this->template->{$key} = $value;

    }
        
    
    protected function getIP()

    {

        $ipaddress = '';



        if (getenv('HTTP_CLIENT_IP'))

            $ipaddress = getenv('HTTP_CLIENT_IP');

        else if(getenv('HTTP_X_FORWARDED_FOR'))

            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');

        else if(getenv('HTTP_X_FORWARDED'))

            $ipaddress = getenv('HTTP_X_FORWARDED');

        else if(getenv('HTTP_FORWARDED_FOR'))

            $ipaddress = getenv('HTTP_FORWARDED_FOR');

        else if(getenv('HTTP_FORWARDED'))

            $ipaddress = getenv('HTTP_FORWARDED');

        else if(getenv('REMOTE_ADDR'))

            $ipaddress = getenv('REMOTE_ADDR');

        else

            $ipaddress = 'UNKNOWN';



        return $ipaddress;

    }
    
    public function createComponentChangePasswordForm()
    {
        $form = $this->userForm->createChangePasswordForm();

        $form->onSuccess[] = function(Nette\Forms\Form $form, $values) {
            $error = '';
            $success = true;

            if (!$this->user->isLoggedIn())
            {
                $error = 'Musíte být přihlášen';
                $success = false;
            }
            else if (!$this->userManager->verifyPassword($this->admin->id, $values->oldPassword))
            {
                $error = 'Špatné staré heslo';
                $success = false;
            }
            else if ($values->newPassword != $values->passwordVerify)
            {
                $error = 'Hesla se neshodují';
                $success = false;
            }
            else
                $this->userManager->changePassword($this->admin->id, $values->newPassword);

            $this->sendJson([
                'success' => $success,
                'error' => $error
            ]);
        };

        return $form;
    }
}
