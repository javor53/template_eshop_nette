<?php
namespace AdminModule;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use App\Forms\SignFormFactory;

class SignPresenter extends BasePresenter
{
    /** @var SignFormFactory @inject */
    public $factory;


    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
            $form = $this->factory->create();
            $form->onSuccess[] = function ($form) {
                    $form->getPresenter()->redirect(':Admin:Homepage:default');
            };
            return $form;
    }


    public function actionOut($from = NULL)
    {
            $this->getUser()->logout();
            if($from == 'public'){
                $this->redirect(':Public:Homepage:default');
            }
    }   
}