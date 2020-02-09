<?php

namespace EshopModule;

use Nette;
use App\EshopModule\Model;
use App\EshopModule\Components\Forms;
use Nette\Application\UI\Form;

class CustomerPresenter extends BasePresenter
{
    
    /** @var Model\CustomerModel @inject */
    public $customerManagerModel;
    
    /** @var Model\ProductModels\ProductCategoryModel @inject */
    public $productCategoryModel;
    
    
    public function renderDefault($id){
        $customers = $this->customerManagerModel->getCustomers();
        
        
        $this->template->customers = $customers;
    } 
    

    
}
