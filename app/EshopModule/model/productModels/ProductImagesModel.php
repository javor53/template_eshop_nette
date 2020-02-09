<?php

namespace App\EshopModule\Model\ProductModels;

use Nette;


class ProductImagesModel extends \App\EshopModule\Model\ManagerModel{
    
    use Nette\SmartObject;

    /**
     * 
     * @param type $productId
     * @return type
     * @throws \Exception
     */
    public function getImagesByProduct($productId) {
        $p = $this->database->query('SELECT * FROM product_images WHERE id_products=?',$productId)->fetchAll();
        if(!$p){
            throw new \Exception('Product image not found');
        }
        return $p;
    }

    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    public function getImage($id){
        $p = $this->database->query('SELECT * FROM product_images WHERE id=?',$id)->fetch();
        if(!$p){
            throw new \Exception('Product image not found');
        }
        return $p;
    }

    /**
     * 
     * @param type $arrForm
     */
    public function insertImage($arrForm){
        try{
            $this->database->table('product_images')->insert([
                'id' => $arrForm[0],
                'dir' => $arrForm[1],
                'title' => $arrForm[2],
                'suffix' => $arrForm[3],
                'id_products' => $arrForm[4] 
            ]);
        }
        catch(\Exception $e)
        {
            
        }
    }

    
}
