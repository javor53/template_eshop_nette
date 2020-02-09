<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class EventForm implements IForm
{
            
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();

        $form->addText('title')->setRequired();
        $form->addText('description');
        $form->addText('date');
        $form->addText('place');
        $form->addInteger('imgId');
        $form->addUpload('file', 'Soubor:')
        ->setRequired();
        $form->addText('img_title', 'Název:')
        ->setRequired();
        
        
        $form->addSubmit('save');
        
        return $form;
    }
    public function createEditForm()
    {
        $form = new Form();
        
        $form->addInteger('id');
        $form->addText('title');
        $form->addTextArea('description');
        $form->addText('date');
        $form->addText('place');
        
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