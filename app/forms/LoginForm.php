<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class LoginForm implements IForm
{
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();

        $form->addText('username', 'Login')->setRequired();
        $form->addPassword('password', 'Heslo')->setRequired();
        $form->addSubmit('logIn', 'Přihlásit');

        return $form;
    }

    /**
     * @param Form $form
     * @param $values
     * @return mixed
     */
    public function formSucceeded(Form $form, $values)
    {
    }
}