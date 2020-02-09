<?php

namespace App\EshopModule\Components\Forms;

use Nette;
use Nette\Application\UI\Form;

class OrderStatusForm implements IForm
{
    
    
    
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();

        $form->addProtection('Platnost formuláře vypršela! Zkuste to prosím znovu.');
        $form->addSubmit('submit');
        $form->addInteger('ids', '');
        $form->addSelect('selStatus','',[
                '1' => 'Nová',
                '2' => 'Zpracovává se',
                '3' => 'Expedovaná',
                '4' => 'Doručená',
                '5' => 'Zrušená'
                ]);
              
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