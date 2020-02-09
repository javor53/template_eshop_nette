<?php

namespace App\EshopModule\Model;

use Nette;


class ItemModel{
    
    use Nette\SmartObject;
    
    public $productManagerModel;
    
    public $quantity = 1;
    public $price;
    
    function __construct(ProductManagerModel $productManagerModel) {
        $this->productManagerModel = $productManagerModel;
    }

    
}
