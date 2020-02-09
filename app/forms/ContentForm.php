<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class ContentForm implements IForm
{
    
    
    
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();

        $form->addText('name')->setRequired();
        $form->addText('content');
        $form->addSubmit('save');
        
        return $form;
    }

    public function createEditForm()
    {
        $form = new Form();

        $form->addText('id')->setRequired();
        $form->addText('title')->setRequired();
        $form->addText('description');
        $form->addSubmit('save');

        return $form;
    }
    public function createContentForm()
    {
        $form = new Form();

        $form->addText('id')->setRequired();
        $form->addText('type_id')->setRequired();
        $form->addText('title')->setRequired();
        $form->addTextArea('description');
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