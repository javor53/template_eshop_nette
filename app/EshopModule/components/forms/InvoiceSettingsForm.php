<?php

namespace App\EshopModule\Components\Forms;

use Nette;
use Nette\Application\UI\Form;

class InvoiceSettingsForm implements IForm
{
    
    
    
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();

        $form->addText('name');
        $form->addText('account_number');

        $form->addText('full_name');
        $form->addText('city');
        $form->addText('zip');
        $form->addText('street_and_number');
        $form->addText('i_number');
        
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