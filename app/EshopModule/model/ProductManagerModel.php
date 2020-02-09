<?php

namespace App\EshopModule\Model;

/*
Database requirements:
 database: products
 * id
 * title
 * subtitle
 * id_product_info
 * id_product_images
 * 
 database: product_images
 * id
 * dir
 * title
 * suffix
 * id_products
 * 
 database: product_info
 * id
 * description
 * 
 *  */


use Nette;
use Nette\Utils\FileSystem;

class ProductManagerModel extends ManagerModel
{
    
    use Nette\SmartObject;   
    
    public $productCategoryModel;
    protected $productInfoModel;
    protected $productImagesModel;
    
    /**
     * 
     * @param Nette\Database\Context $database
     * @param \App\EshopModule\Model\ProductModels\ProductCategoryModel $productCategoryModel
     * @param \App\EshopModule\Model\ProductModels\ProductImagesModel $productImagesModel
     * @param \App\EshopModule\Model\ProductModels\ProductInfoModel $productInfoModel
     */
    function __construct(
        Nette\Database\Context $database, 
        ProductModels\ProductCategoryModel $productCategoryModel,
        ProductModels\ProductImagesModel $productImagesModel,
        ProductModels\ProductInfoModel $productInfoModel) {
        
        parent::__construct($database);
        
        $this->productCategoryModel = $productCategoryModel;
        $this->productInfoModel = $productInfoModel;
        $this->productImagesModel = $productImagesModel;
        
        $this->database = $database;
 
    }
    
    /**
     * 
     * @return type
     * @throws \Exception
     */
    function getProducts() {
        $p = $this->database->query('SELECT * FROM w_products')->fetchAll();
        if(!$p){
            throw new \Exception('Product not found');
        }
        return $p;
    }
    
    /**
     * 
     * @return type
     * @throws \Exception
     */
    function getProductsStock() {
        $p = $this->database->query('SELECT * FROM w_products_stock')->fetchAll();
        if(!$p){
            throw new \Exception('Product stock not found');
        }
        return $p;
    }
    
    function getProductsCategory() {
        $p = $this->database->query('SELECT * FROM w_products_category')->fetchAll();
        if(!$p){
            throw new \Exception('Product category not found');
        }
        return $p;
    }
    
    /**
     * 
     * @return type
     * @throws \Exception
     */
    function getProductsPrice() {
        $p = $this->database->query('SELECT * FROM w_products_price')->fetchAll();
        if(!$p){
            throw new \Exception('Product price not found');
        }
        return $p;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    function getProduct($id) {
        $p = $this->database->query('SELECT * FROM products WHERE id=?',$id)->fetch();
        if(!$p){
            throw new \Exception('Product not found');
        }
        return $p;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    function getProductPrice($id) {
        $p = $this->database->query('SELECT o.* FROM Products p,Product_price o WHERE o.id=p.product_price_id AND p.id=?',$id)->fetch();
        if(!$p){
            throw new \Exception('ProductPrice not found');
        }
        return $p;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    function getProductInfo($id) {
        $p = $this->database->query('SELECT o.* FROM Products p,Product_info o WHERE o.id=p.product_info_id AND p.id=?',$id)->fetch();
        if(!$p){
            throw new \Exception('ProductPrice not found');
        }
        return $p;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    function getProductStock($id) {
        $p = $this->database->query('SELECT o.* FROM Products p,Product_stock o WHERE o.id=p.product_stock_id AND p.id=?',$id)->fetch();
        if(!$p){
            throw new \Exception('ProductStock not found');
        }
        return $p;
    }
    

    
    /**
     * 
     * @return int
     * @throws \Exception
     */
    function getProductsCount() : int{
        $p = $this->database->table('products')->count('*');
          
        if(!$p){
            throw new \Exception('Product not found');
        }
        return $p;    
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    function getProductImages($id) {
        $p = $this->database->query('SELECT * FROM product_images WHERE product_id=?',$id);
        if(!$p){
            throw new \Exception('ProductImages not found');
        }
        return $p;
    }
    
    /**
     * 
     * @param type $title
     * @return bool
     */
    public function productImageExists($title) : bool{
        $exist = $this->database->query('SELECT title FROM product_images WHERE title=?',$title)->fetch();          
        if(!$exist){
            return false;
        }else{
            return true;
        }        
    }    
    
    /**
     * 
     * @param type $title
     * @param type $subtitle
     * @param type $description
     */
    function createProduct($title, $subtitle, $description){
            try{
                $this->database->table('product_price')->insert([
                    'price' => 0,
                    'cost_price' => 0,
                    'standard_price' => 0,
                    'special_price' => 0
                 ]);
                
                $this->database->table('product_info')->insert([
                    'id' => NULL
                ]);
                
                $this->database->table('product_stock')->insert([
                    'id' => NULL,
                    'actual_quantity' => 0,
                ]);
                
                $priceId = $this->database->query('SELECT id FROM product_price ORDER BY id DESC LIMIT 1')->fetch();
                $infoId = $this->database->query('SELECT id FROM product_info ORDER BY id DESC LIMIT 1')->fetch();
                $stockId = $this->database->query('SELECT id FROM product_stock ORDER BY id DESC LIMIT 1')->fetch();      
                
                $this->database->table('products')->insert([
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'description' => $description,
                    'product_price_id' => $priceId->id,
                    'product_info_id' => $infoId->id,
                    'product_stock_id' => $stockId->id,
                    'product_category_id' => 0    
                 ]);         
            }
            catch(\Exception $e)
            {
                
            }
    }
    
    /**
     * 
     * @param type $id
     * @param type $title
     * @param type $subtitle
     * @param type $description
     * @param type $price
     * @param type $cost_price
     * @param type $st_price
     * @param type $sp_price
     */
    function editProduct($id,$title, $subtitle, $description) {
        try{
                $this->database->table('products')->where('id',$id)->update([
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'description' => $description,
                 ]);
            }
            catch(\Exception $e)
            {
                
            }
    }
    
    /**
     * 
     * @param type $ids
     * @param type $category
     */
    function updateCategory($ids,$category) {
        $i=0;
        //$ids = explode(',', $ids);
        foreach ($ids as $id) {
            $this->database->query('UPDATE products SET '
                    . 'product_category_id=? WHERE id=?',$category[$i],$id);
            $i++;
        }
    }
    
    /**
     * 
     * @param type $ids
     * @param type $quantity
     * @param type $min
     * @param type $max
     * @param type $availability
     */
    function updateStock($ids,$quantity,$min,$max,$availability) {
        $i=0;
        //$ids = explode(',', $ids);
        foreach ($ids as $id) {
            $idStock = $this->database->query('SELECT product_stock_id FROM products WHERE id=?',$ids[$i])->fetch();
            $stock = $this->database->query('SELECT actual_quantity FROM product_stock WHERE id=?',$idStock->product_stock_id)->fetch();
            $this->database->query('UPDATE product_stock SET '
                    . 'actual_quantity=?,'
                    . 'min_quantity=?,'
                    . 'max_quantity=?,'
                    . 'availability=? WHERE id=?',$quantity[$i],$min[$i],$max[$i],$availability[$i],$idStock->product_stock_id);
            
            $check = $quantity[$i] - $stock->actual_quantity;
            if($check != 0){
                if($check>0)
                    $check = "+" . $check;
                $this->logProductStock($ids[$i], $check , 'změna stavu');
            }
            $i++;
        }
    }
    
    /**
     * 
     * @param type $ids
     * @param type $sale
     * @param type $tip
     * @param type $visibility
     */
    function updateProductInfo($ids,$sale,$tip,$visibility) {
        $i=0;
        //$ids = explode(',', $ids);
        foreach ($ids as $id) {
            $idInfo = $this->database->query('SELECT product_info_id FROM products WHERE id=?',$ids[$i])->fetch();
            $this->database->query('UPDATE product_info SET '
                    . 'sale=?,'
                    . 'tip=?,'
                    . 'visibility=? WHERE id=?',$sale[$i],$tip[$i],$visibility[$i],$idInfo->product_info_id);
            $i++;
        }
    }
    
    /**
     * 
     * @param type $ids
     * @param type $price
     * @param type $costPrice
     * @param type $stPrice
     * @param type $spPrice
     */
    function updatePrice($ids,$price,$costPrice,$stPrice,$spPrice) {
        $i=0;
        //$ids = explode(',', $ids);
        foreach ($ids as $id) {
            $idPrice = $this->database->query('SELECT product_price_id FROM products WHERE id=?',$ids[$i])->fetch();
            $this->database->query('UPDATE product_price SET '
                    . 'price=?,'
                    . 'cost_price=?,'
                    . 'standard_price=?,'
                    . 'special_price=? WHERE id=?',$price[$i],$costPrice[$i],$stPrice[$i],$spPrice[$i],$idPrice->product_price_id);
            $i++;
        }
    }
    
    /**
     * 
     * @param type $id
     * @param type $quantity
     */
    function addStock($id,$quantity) {
        $productStock = $this->getProductStock($id);
        $q = $quantity;
        $quantity += $productStock->actual_quantity; 
        
        $this->database->query('UPDATE product_stock SET actual_quantity=? WHERE id=?',$quantity,$productStock->id);
        
        $this->logProductStock($id, "+" . $q, 'naskladnění');
    }
    
    /**
     * 
     * @param type $title
     * @param type $dir
     * @param type $suffix
     * @param type $productId
     */
    function createProductImage($title,$dir,$suffix,$productId) {
        try{
            $this->database->table('product_images')->insert([
                    'dir' => $dir,
                    'title' => $title,
                    'suffix' => $suffix,
                    'product_id' => $productId
                 ]);
        }catch(\Exception $e){
            
        }
    }
    
    /**
     * 
     * @param type $id
     * @return bool
     */
    function productExists($id){
        $p = $this->database->query('SELECT id FROM products WHERE id=?',$id)->fetch();
        if(!$p){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * 
     * @param type $id
     */
    function deleteProduct($id) : bool{
        try{
            if($this->getProductsCount() == 1){
                return false;
            }
            
            $priceId = $this->database->query('SELECT product_price_id FROM products WHERE id=?',$id)->fetch();
            $stockId = $this->database->query('SELECT product_stock_id FROM products WHERE id=?',$id)->fetch();
            $infoId =  $this->database->query('SELECT product_info_id FROM products WHERE id=?',$id)->fetch();  
            
            $this->database->query('DELETE FROM product_price WHERE id=?',$priceId->product_price_id);
            $this->database->query('DELETE FROM product_stock WHERE id=?',$stockId->product_stock_id);
            $this->database->query('DELETE FROM product_info WHERE id=?',$infoId->product_info_id);
            
            $this->database->query('DELETE FROM products WHERE id=?',$id);
        }
        catch(\Exception $e)
        {
            
        }
        return true;
    }
    
    /**
     * 
     * @param type $id
     */
    function deleteProductImage($id){
        try{
            $this->database->query('DELETE FROM product_images WHERE id=?',$id);
        }
        catch(\Exception $e)
        {
            
        }
    }
    
    /**
     * 
     * @param type $id
     * @param type $quantity
     * @param type $note
     */
    function logProductStock($id,$quantity,$note){
        try {
            $content = FileSystem::read('log/stocklog.txt');
            $content .= $id . "," . $quantity . "," . $note . "\r\n";
            FileSystem::write('log/stocklog.txt',$content);
        } catch (\Exception $e) {

        }
    
    }

    
}
