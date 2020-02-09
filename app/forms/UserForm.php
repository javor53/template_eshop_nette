<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class UserForm implements IForm
{
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();

        $form->addText('username')->setRequired();
        $form->addPassword('password')->setRequired();
        $form->addPassword('passwordVerify')->setRequired();
        $form->addText('firstName')->setRequired();
        $form->addText('lastName')->setRequired();
        $form->addText('email')->setRequired();
        $form->addText('phone');
        $form->addSelect('role','Role', [
                            'supervisor' => 'Supervisor',
                            'admin' => 'Admin',
                            'journalist' => 'Journalist',
                        ])->setRequired();
        $form->addSubmit('save');

        return $form;
    }

    public function createEditForm()
    {
        $form = new Form();

        $form->addText('id')->setRequired();
        $form->addText('username')->setRequired();
        $form->addCheckbox('originalPassword');
        $form->addPassword('password');
        $form->addPassword('passwordVerify');
        $form->addText('firstName')->setRequired();
        $form->addText('lastName')->setRequired();
        $form->addText('email')->setRequired();
        $form->addText('phone');
        $form->addSelect('role','Role', [
                            'supervisor' => 'Supervisor',
                            'admin' => 'Admin',
                            'journalist' => 'Journalist'
                        ])->setRequired();
        $form->addSubmit('save');

        return $form;
    }

    public function createChangePasswordForm()
    {
        $form = new Form();

        $form->addPassword('oldPassword')->setRequired();
        $form->addPassword('newPassword')->setRequired();
        $form->addPassword('passwordVerify')->setRequired();
        $form->addSubmit('save');

        return $form;
    }

    /**
     * @param Form $form
     * @param $values
     */
    public function formSucceeded(Nette\Application\UI\Form $form, $values)
    {
    }
}