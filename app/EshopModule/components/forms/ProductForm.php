<?php

namespace App\EshopModule\Components\Forms;

use Nette;
use Nette\Application\UI\Form;

class ProductForm implements IForm
{
            
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();

        $form->addText('title', 'Název:')
        ->setRequired();
        $form->addText('subtitle', 'Podnadpis:');
        $form->addText('description', 'Popis:');
       
              
        $form->addSubmit('save');
        
        
        
        return $form;
    }
    
    public function createEdit()
    {
        $form = new Form();

        $form->addInteger('id');
        $form->addText('title', 'Název:')
        ->setRequired();
        $form->addText('subtitle', 'Podnadpis:');
        $form->addTextArea('description', 'Popis:');
        
        $form->addUpload('file', 'Soubor:');
        $form->addText('imageTitle', 'Název:');
        
        $form->addInteger('price');
        $form->addInteger('cost_price');
        $form->addInteger('standard_price');
        $form->addInteger('special_price');
        
        $form->addInteger('quantity');
        $form->addInteger('min_quantity');
        $form->addInteger('max_quantity');
        $form->addSelect('availability', 'Availability', [
            'unavailable' => 'Nedostupné',
            'out-of-stock' => 'Vyprodáno',
            'in-stock' => 'Skladem'
        ]);
      
        $form->addSubmit('save');
        
        return $form;
    }
    
    public function addStock()
    {
        $form = new Form();

        $form->addInteger('id')->setRequired();
        
        $form->addInteger('quantity')->setRequired();
              
        $form->addSubmit('save');
        
        return $form;
    }
    
    public function addCategory() {
        $form = new Form();
        $form->addText('title')->setRequired();
        $form->addTextArea('description');
        $form->addSubmit('save');
        
        return $form;        
    }
    
    public function editCategory() {
        $form = new Form();
        $form->addInteger('id')->setRequired();
        $form->addText('name')->setRequired();
        $form->addTextArea('desc');
        $form->addSubmit('submit');
        
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