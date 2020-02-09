<?php

namespace EshopModule;

use Nette;
use App\EshopModule\Model;

final class HomepagePresenter extends BasePresenter
{
    
    /** @var Model\ProductManagerModel @inject */
    public $productManagerModel;
    
    /** @var Model\ProductModels\ProductCategoryModel @inject */
    public $productCategoryModel;
    
    public function renderDefault($id){
        
        
    } 


    
}
