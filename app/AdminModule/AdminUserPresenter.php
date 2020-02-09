<?php

namespace AdminModule;

use Nette;
use App\Forms;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;
use App\Model\LoginFailModel;

class AdminUserPresenter extends BasePresenter
{

    /** @var Forms\LoginForm @inject */
    public $loginForm;
    
    /** @var Model\LoginFailModel @inject */

    public $loginFailModel;
    
    /** @var Model\UserManager @inject */

    public $userManager;

    public function renderDefault()
    {
        
    }

    public function renderUserTable()
    {
        
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        
        $users = $this->userManager->getUsers();

        $roles = [
            'supervisor' => 'Supervisor',
            'admin' => 'Admin',
            'journalist' => 'Journalist',
        ];

        foreach ($users as $u){
            $u->roleName = $roles[$u->role];
        }
        $this->render([
            'users' => $users
        ]);
    }

    public function renderLogin()
    {

        $this->loginFailModel->deleteOld();
        $fail = $this->loginFailModel->getByIP();

        $this->render([
            'enableLogin' => !$fail || $fail->count < LoginFailModel::ATTEMPTS,
            'waitTime' => LoginFailModel::WAIT_TIME
        ]);
    }

    /**
     * @return Nette\Application\UI\Form
     */
    public function createComponentLoginForm()
    {
        $form = $this->loginForm->create();

        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $this->loginFailModel->deleteOld();
                $this->user->setExpiration(0, TRUE);
                $this->user->login($values->username, $values->password);
                $this->loginFailModel->deleteOld();
                $this->redirect(':Admin:Homepage:default');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }

    public function actionLogout()
    {
        $this->user->logout(true);
        $this->redirect('this');
    }

    /**
     * @return Form
     */
    public function createComponentNewUserForm()
    {
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = $this->userForm->create();

        $form->onSuccess[] = function(Form $form, $values) {
            if ($values->password != $values->passwordVerify){
                $this->flashMessage('Hesla se neshodují', 'fail');
                return;
            }
                //$this->sendJson(['success' => false, 'msg' => 'Hesla se neshodují']);

            if (!empty($this->userManager->getByUsername($values->username))){
                $this->flashMessage('Uživatelské jméno nelze použít', 'fail');
                return;
            }
                //$this->sendJson(['success' => false, 'msg' => 'Uživatelské jméno nelze použít']);

            if (!empty($this->userManager->getByEmail($values->email))){
                $this->flashMessage('E-mail jméno nelze použít', 'fail');
                return;
            }
                //$this->sendJson(['success' => false, 'msg' => 'E-mail nelze použít']);

            $this->userManager->add(
                $values->username,
                $values->password,
                $values->email,
                $values->firstName,
                $values->lastName,
                $values->role,
                (isset($values->phone) && !empty($values->phone) && $values->phone != null) ? $values->phone : null
            );

            /*$this->sendJson([
                'success' => true,
            ]);*/
            $this->redirect(':Admin:AdminUser:userTable');
        };
        
        $form->onError[] = function(Form $form) {
            $this->sendJson([
                'success' => false,
                'msg' => 'Chyba při zpracovávání formuláře'
            ]);
        };

        return $form;
    }

    /**
     * @return Form
     */
    public function createComponentEditUserForm()
    {
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = $this->userForm->createEditForm();

        $form->onSuccess[] = function(Form $form, $values) {
            $user = $this->userManager->getByID($values->id);

            if (!$user){
                $this->flashMessage('Uživatel neexistuje', 'fail');
                return;
            }    
            //$this->sendJson(['success' => false, 'msg' => 'Uživatel neexistuje']);

            if (!empty($this->userManager->getByUsername($values->username)) && $user->username != $values->username){
                $this->flashMessage('Uživatelské jméno nelze použít', 'fail');
                return;
            }
                //$this->sendJson(['success' => false, 'msg' => 'Uživatelské jméno nelze použít']);

            if (!empty($this->userManager->getByEmail($values->email)) && $user->email != $values->email){
                $this->flashMessage('E-mail jméno nelze použít', 'fail');
                return;
            }
                //$this->sendJson(['success' => false, 'msg' => 'E-mail jméno nelze použít']);

            if ($values->originalPassword == false)
            {
                
                if ($values->password != $values->passwordVerify){
                    //$this->sendJson(['success' => false, 'msg' => 'Hesla se neshodují']);
                    $this->flashMessage('Hesla se neshodují', 'fail');
                    $this->redirect('this');
                }
                else{
                    $this->userManager->changePassword($values->id, $values->password);
                    $this->flashMessage('Úpravy byly uloženy', 'success');
                    }
            }

            
            $this->userManager->edit(
                $values->id,
                $values->username,
                $values->email,
                $values->firstName,
                $values->lastName,
                $values->role,
                $values->phone
                //(isset($values->phone) && !empty($values->phone) && $values->phone != null) ? $values->phone : null
            );
            
            $this->redirect(':Admin:AdminUser:userTable');
            
           /* $this->sendJson([
                'success' => true,
            ]);*/
        };

        $form->onError[] = function(Form $form) {
            $this->sendJson([
                'success' => false,
                'msg' => 'Chyba při zpracovávání formuláře'
            ]);
        };

        return $form;
    }

    public function actionEditData($id)
    {
        if ($this->user->isLoggedIn() && $this->admin->role == 'supervisor')
        {
            $success = true;
        
            $user = $this->userManager->getByID($id);
            $success = !$user ? false : true;

            $this['editUserForm']->setDefaults(array(
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'role' => $user->role,
                'phone' => $user->phone,
                ));

            /*$this->sendJson([
                'success' => $success,
                'msg' => $success ? '' : 'Uživatel neexistuje',
                'user' => $success ? $user : null
            ]);*/
        }
        else
            $success = false;

    }

    public function actionDelete($id)
    {
        if(count($this->userManager->getUsers()) <= 2 || $this->user->getId() == $id){
            $success = false;
            $this->flashMessage('Uživatele nelze smazat.', 'fail');
            $this->redirect(':Admin:AdminUser:userTable');
        }
        if ($this->user->isLoggedIn() && $this->admin->role == 'supervisor')
        {
            $success = true;
            $this->userManager->delete($id);
        }
        else
            $success = false;

        //$this->sendJson(['success' => $success]);
        $this->redirect(':Admin:AdminUser:userTable');
    }
}