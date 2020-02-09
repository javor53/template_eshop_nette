<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class ImageUploadForm implements IForm
{
            
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();

        $form->addUpload('file', 'Soubor:')
        ->setRequired();
        $form->addText('title', 'Název:')
        ->setRequired();
              
        $form->addSubmit('upload');
        
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