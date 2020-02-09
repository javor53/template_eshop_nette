<?php

namespace App\EshopModule\Model;

use Nette;


class CartModel{
    
    use Nette\SmartObject;
    
    /** @var Nette\Http\Session */
    private $session;

    /** @var Nette\Http\SessionSection */
    private $sectionCart;
    
    public $productManagerModel;
        
    function __construct(ProductManagerModel $productManagerModel, Nette\Http\Session $session) {
        $this->productManagerModel = $productManagerModel;
        
        $this->session = $session;
        $this->sectionCart = $session->getSection('cart')->setExpiration('2 days');
    }
    
    /**
     * 
     * @return type
     */
    function getCart(){
        return $this->sectionCart;
    }
          
    /**
     * 
     * @param type $id
     * @param type $count
     */
    function add($id, $count) {
        if(!isset($this->sectionCart->$id)){
            $this->sectionCart->$id = $count;
        }else{
            $this->sectionCart->$id += $count; 
        }
    }   
    
    /**
     * 
     * @param type $id
     */
    function remove($id){
        unset($this->sectionCart->$id); 
    }
    

    function emptyCart(){
        foreach ($this->sectionCart as $key => $value) {
            unset($this->sectionCart->$key); 
        }
        
    }
    
    /**
     * 
     * @param type $values
     */
    function recount($values) {
        $i = 0;
        
        foreach ($this->sectionCart as $key => $value) {
            if($values[$i] != 0){
                $this->sectionCart->$key = $values[$i];
            }else{
                unset($this->sectionCart->$key); 
            }
            $i++;             
        }        
    }
    
    /**
     * 
     * @return array
     */
    function getData() : array{
        $arr = array();
        $i = 0;
        foreach ($this->sectionCart as $key => $value) {
            $p = $this->productManagerModel->getProduct($key);
            $price = $this->productManagerModel->getProductPrice($key);
            $arr[$i] = ['id' => $key,
                        'title' => $p->title,
                        'price' => $price->price*$value,
                        'quantity' => $value];
            $i++;
       }
        return $arr;
    }
    
    /**
     * 
     * @return float
     */
    function getTotalPrice() : float {
        $price = 0;
        foreach ($this->sectionCart as $key => $value) {
            $p= $this->productManagerModel->getProductPrice($key);
            $price += $value*$p->price; //DODĚLAT Price u produktu 
        }
        return $price;
    }

    
}
