<?php

namespace App\EshopModule\Components\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\EshopModule\Model;

class OrderForm implements IForm
{
    /** @var Model\ShippingInfoModel @inject */
    public $shippingInfoModel;
    
    
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();

        $form->addText('full_name')
        ->setRequired();
        $form->addText('email')->setRequired();
        
        $form->addSelect('payment', 'Payment', [
            'transfer-to-account' => 'Převodem na účet',
            'cash-on-delivery' => 'Dobírka',
        ]);
        $form->addSelect('transport','Transport', [
            'ppl' => 'PPL',
            'cz-post' => 'Česká pošta',
        ]);//SJEDNOTIT s SHIPPING MODELEM !!!!
        
        $form->addText('city')->setRequired();
        $form->addText('street')->setRequired();
        $form->addText('street_number')->setRequired();
        $form->addInteger('zip')->setRequired();
        $form->addText('phone')->setRequired();
        $form->addText('note');
        
        $form->addCheckbox('isComp');
        $form->addText('comp_name');
        $form->addInteger('i_number');
        $form->addInteger('vat');
        
        $form->addCheckbox('isDelivery');
        $form->addText('d_city');
        $form->addText('d_street');
        $form->addText('d_street_number');
        $form->addInteger('d_zip');
        
        $form->addSubmit('save');
        
        return $form;
    }
    
    public function createEmail() {
        $form = new Form();

        $form->addText('subject')
        ->setRequired();
        $form->addTextArea('content');
        
        $form->addSelect('type', 'Type', [
                '1' => 'Nová',
                '2' => 'Zpracovává se',
                '3' => 'Expedovaná',
                '4' => 'Doručená',
                '5' => 'Zrušená'
        ]);
        
              
        $form->addSubmit('save');
        
        return $form;
    }
    
     public function createEditEmail() {
        $form = new Form();
        $form->addHidden('id');
        $form->addText('subject')
        ->setRequired();
        $form->addTextArea('content');
        
        $form->addSelect('type', 'Type', [
                '1' => 'Nová',
                '2' => 'Zpracovává se',
                '3' => 'Expedovaná',
                '4' => 'Doručená',
                '5' => 'Zrušená'
        ]);
        
              
        $form->addSubmit('submit');
        
        return $form;
    }
    
    public function createEditEmailSetting() {
        $form = new Form();
        $form->addHidden('id');
        $form->addText('host');
        $form->addText('username');
        $form->addText('password');
        $form->addText('secure');
        $form->addText('name');
        
        $form->addSubmit('send');
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