<?php

namespace EshopModule;

use Nette;
use App\EshopModule\Model;
use App\EshopModule\Components\Forms;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Control;
use Nette\Utils\Image;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;

class ProductPresenter extends BasePresenter
{
    
    /** @var Model\ProductManagerModel @inject */
    public $productManagerModel;
        
    /** @var Forms\ProductForm @inject */
    public $productForm;
    
    /** @var Forms\ImageUploadForm @inject */
    public $imageUploadForm;
    
    public $stockAvailability = [
        'unavailable' => 'Nedostupné',
        'out-of-stock' => 'Vyprodáno',
        'in-stock' => 'Skladem',
    ];
    
    public $productVisibility = [
        'private' => 'Soukromé',
        'public' => 'Veřejné',
    ];
    
    public $categories;


    public function handleEditProductCategory($id) {
        
        $category = $this->productManagerModel->productCategoryModel->getCategory($id);

        $this['editProductCategoryForm']->setDefaults([
            'id' => $category->id,
            'name' => $category->title,
            'desc' => $category->description,
        ]); 

        if ($this->isAjax()) {
            $this->redrawControl('category');
        }else {
            $this->redirect('this');
        }
    }
    
    public function handleDeleteProductImage($id,$imgId,$dir) {
        
        FileSystem::delete($dir);
        $this->productManagerModel->deleteProductImage($imgId);
        
        $productImages = $this->productManagerModel->getProductImages($id);
        $this->template->productImages = $productImages;
        
        if ($this->isAjax()) {
            $this->redrawControl('productImages');
        }else {
            $this->redirect('this');
        }
    }
    
    public function handleSaveCategoryForm(){
        $products = $this->productManagerModel->getProductsCategory();
        $this->template->products = $products;
        
        if ($this->isAjax()) {
            $this->redrawControl('categories');
        }else {
            $this->redirect('this');
        }
    }
    
    public function handleSaveStockForm(){
        $products = $this->productManagerModel->getProductsStock();
        $this->template->products = $products;
        
        if ($this->isAjax()) {
            $this->redrawControl('stocks');
        }else {
            $this->redirect('this');
        }
    }
    
    public function handleSaveProductInfoForm(){
        $products = $this->productManagerModel->getProducts();
        $this->template->products = $products;
        
        if ($this->isAjax()) {
            $this->redrawControl('productInfo');
        }else {
            $this->redirect('this');
        }
    }
    
    public function handleSavePriceForm(){
        $products = $this->productManagerModel->getProductsPrice();
        $this->template->products = $products;
        
        if ($this->isAjax()) {
            $this->redrawControl('prices');
        }else {
            $this->redirect('this');
        }
    }


    public function renderDefault($id){
        
        $products = $this->productManagerModel->getProducts();

        $this->template->products = $products;
        
        $i = 0;
        foreach ($products as $product) {
            $this["productInfoMultiplierForm"]['info'][$i]->setDefaults(array(
                'selVisibility' => $product->visibility,
                'sale' => $product->sale,
                'tip' => $product->tip,
                )); 
            $i++;
        }
    } 
    
    public function renderDetail($id) {
        $product = $this->productManagerModel->getProduct($id);
        $price = $this->productManagerModel->getProductPrice($id);
        $stock = $this->productManagerModel->getProductStock($id);
        $productImages = $this->productManagerModel->getProductImages($id);

        $this['editProductForm']->setDefaults([
            'id' => $product->id,
            'title' => $product->title,
            'subtitle' => $product->subtitle,
            'description' => $product->description,
            'price' => $price->price,
            'cost_price' => $price->cost_price,
            'standard_price' => $price->standard_price,
            'special_price' => $price->special_price,
            'quantity' => $stock->actual_quantity,
            'min_quantity' => $stock->min_quantity,
            'max_quantity' => $stock->max_quantity,
            'availability' => $stock->availability,
        ]); 
        
        
        $this->template->product = $product;
        if (!isset($this->template->productImages)){
            $this->template->productImages = $productImages;
        }
        
        
    }
    
    public function renderPrice() {
        $products = $this->productManagerModel->getProductsPrice();
 
        $this->template->products = $products;
        
        $i = 0;
        foreach ($products as $product) {
            $this["priceMultiplierForm"]['price'][$i]->setDefaults(array(
                'price' => $product->price,
                'cost_price' => $product->cost_price,
                'standard_price' => $product->standard_price,
                'special_price' => $product->special_price
                )); 
            $i++;
        }
    }
    
    public function renderStock(){
        $products = $this->productManagerModel->getProductsStock();
 
        $this->template->products = $products;
        $this->template->configAvailability = $this->stockAvailability;
        
        $i = 0;
        foreach ($products as $product) {
            $this["stockMultiplierForm"]['stock'][$i]->setDefaults(array(
                'selAvailability' => $product->availability,
                'inQuantity' => $product->actual_quantity,
                'inMin' => $product->min_quantity,
                'inMax' => $product->max_quantity
                )); 
            $i++;
        }
    }
    
    public function renderCategory() {
        $this->template->categories = $this->productManagerModel->productCategoryModel->getCategories();
        
        $products = $this->productManagerModel->getProductsCategory();
 
        $this->template->products = $products;
        $i = 0;
        foreach ($products as $product) {
            $this["categoryMultiplierForm"]['category'][$i]->setDefaults(array(
                'selCategory' => $product->product_category_id,
                )); 
            $i++;
        }
        
    }
    
    
    public function createComponentAddProductForm() {
        
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = $this->productForm->create();
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {   
                $this->productManagerModel->createProduct(
                        $values->title,
                        $values->subtitle,
                        $values->description
                        );
                $this->flashMessage('Produkt byl přidaný.', 'success');
                $this->redirect('Product:default');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
    
    public function createComponentAddProductCategoryForm() {
        
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = $this->productForm->addCategory();
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {   
                $this->productManagerModel->productCategoryModel->createCategory(
                        $values->title,
                        $values->description
                        );
                
                $this->flashMessage('Kategorie byla přidaná.', 'success');
                $this->redirect('Product:category');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
    
    public function createComponentEditProductCategoryForm() {
        
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = $this->productForm->editCategory();
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {   
                $this->productManagerModel->productCategoryModel->updateCategory(
                        $values->id,
                        $values->name,
                        $values->title
                        );
                
                $this->flashMessage('Kategorie byla editována.', 'success');
                $this->redirect('Product:category');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
    
    public function createComponentEditProductForm($id) {
        
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = $this->productForm->createEdit();
            
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {   
                $this->productManagerModel->editProduct(
                        $values->id,
                        $values->title,
                        $values->subtitle,
                        $values->description
                        );
                
                $availability[0] = $values->availability;
                $quantity[0] = $values->quantity;
                $min[0] = $values->min_quantity;
                $max[0] = $values->max_quantity;
                $price[0] = $values['price'];
                $costPrice[0] = $values['cost_price'];
                $stPrice[0] = $values['standard_price'];
                $spPrice[0] = $values['special_price'];
                $ids[0] = $values->id;
                
                $this->productManagerModel->updatePrice(
                        $ids,
                        $price,
                        $costPrice,
                        $stPrice,
                        $spPrice
                        );
                
                $this->productManagerModel->updateStock(
                        $ids,
                        $quantity,
                        $min,
                        $max,
                        $availability
                        );
                
                $file = $values->file;
                $file_ext = strtolower(mb_substr($file->getSanitizedName(), strrpos($file->getSanitizedName(), ".")));
                
                if($file->getName() == ''){
                    
                    return;
                }
                
                if($values->imageTitle == ''){
                    $file_title = explode('.', $file->getName());
                    $values->imageTitle = $file_title[0];
                }

                if($this->productManagerModel->productImageExists($values->imageTitle . $file_ext)){

                    $this->flashMessage('Chyba: Zvolte jiný název obrázku.', 'fail');
                    
                    return;
                }
                

                $file->move('img/product_img/' . $values->imageTitle . $file_ext);
                $path = "img/product_img/";

                $img = Image::fromFile('img/product_img/' . $values->imageTitle . $file_ext);
                $img->save('img/product_img/' . $values->imageTitle . $file_ext);
                
                $this->productManagerModel->createProductImage($values->imageTitle, $path, $file_ext, $values->id);
                
                $this->flashMessage('Změny byly uloženy.', 'success');
                $this->redirect('this');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };
        return $form;
    }
    
     public function createComponentCategoryMultiplierForm() {
        
        $form = new Nette\Application\UI\Form;

        $numOfProducts = $this->productManagerModel->getProductsCount();
        $this->categories = $this->productManagerModel->productCategoryModel->getCategoriesArray();
        $this->categories[0] = 'Nezařazeno';
        
        $form->addMultiplier('category', function (Nette\Forms\Container $container, Nette\Forms\Form $form) {     
            $container->addSelect('selCategory','',$this->categories);
            $container->addHidden('ids');
        },$numOfProducts);

        $form->addSubmit('submit', 'uložit');
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $values = $form->getValues();
                $i=0;
                foreach ($values['category'] as $value) {   
                    $category[$i] = $value['selCategory'];
                    $ids[$i] = $value['ids'];
                    $i++;
                }
                
                $this->productManagerModel->updateCategory($ids, $category);
                $this->flashMessage('Změny byly uloženy.', 'success');
                $this->handleSaveCategoryForm();    
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };   
        
        return $form;
    }
    
    public function createComponentStockMultiplierForm() {
        
        $form = new Nette\Application\UI\Form;

        $numOfProducts = $this->productManagerModel->getProductsCount();
        
        $form->addMultiplier('stock', function (Nette\Forms\Container $container, Nette\Forms\Form $form) {
            $container->addSelect('selAvailability','',$this->stockAvailability);
            $container->addInteger('inQuantity')
                      ->setAttribute('class', 'form-control form-text-small');
            $container->addInteger('inMin')
                      ->setAttribute('class', 'form-control form-text-small');
            $container->addInteger('inMax')
                      ->setAttribute('class', 'form-control form-text-small');
            $container->addHidden('ids');
        },$numOfProducts);

        $form->addSubmit('submit', 'uložit');
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $values = $form->getValues();
                $i=0;
                foreach ($values['stock'] as $value) {   
                    $availability[$i] = $value['selAvailability'];
                    $quantity[$i] = $value['inQuantity'];
                    $min[$i] = $value['inMin'];
                    $max[$i] = $value['inMax'];
                    $ids[$i] = $value['ids'];
                    $i++;
                }

                $this->productManagerModel->updateStock($ids, $quantity, $min, $max, $availability);
                $this->flashMessage('Změny byly uloženy.', 'success');
                $this->handleSaveStockForm();    
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };   
        
        return $form;
    }
    
    public function createComponentProductInfoMultiplierForm() {
        
        $form = new Nette\Application\UI\Form;

        $numOfProducts = $this->productManagerModel->getProductsCount();
        
        $form->addMultiplier('info', function (Nette\Forms\Container $container, Nette\Forms\Form $form) {
            $container->addSelect('selVisibility','',$this->productVisibility);
            $container->addCheckbox('sale')
                    ->setAttribute('class', 'form-control form-checkbox');
            $container->addCheckbox('tip')
                    ->setAttribute('class', 'form-control form-checkbox');
            $container->addHidden('ids');
        },$numOfProducts);

        $form->addSubmit('submit', 'uložit');
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $values = $form->getValues();
                $i=0;
                foreach ($values['info'] as $value) {   
                    $visibility[$i] = $value['selVisibility'];
                    $sale[$i] = $value['sale'];
                    $tip[$i] = $value['tip'];
                    $ids[$i] = $value['ids'];
                    $i++;
                }

                $this->productManagerModel->updateProductInfo($ids, $sale, $tip, $visibility);
                $this->handleSaveProductInfoForm();
                $this->flashMessage('Změny byly uloženy.', 'success');
                $this->redirect('Product:default');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };   
        
        return $form;
    }
    
     public function createComponentPriceMultiplierForm() {
        
        $form = new Nette\Application\UI\Form;

        $numOfProducts = $this->productManagerModel->getProductsCount();
        
        $form->addMultiplier('price', function (Nette\Forms\Container $container, Nette\Forms\Form $form) {
            $container->addInteger('price')
                      ->setAttribute('class', 'form-control form-text-medium');
            $container->addInteger('cost_price')
                      ->setAttribute('class', 'form-control form-text-medium');
            $container->addInteger('standard_price')
                      ->setAttribute('class', 'form-control form-text-medium');
            $container->addInteger('special_price')
                      ->setAttribute('class', 'form-control form-text-medium');
            $container->addHidden('ids');
        },$numOfProducts);

        $form->addSubmit('submit', 'uložit');
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $values = $form->getValues();
                $i=0;
                foreach ($values['price'] as $value) {   
                    $price[$i] = $value['price'];
                    $costPrice[$i] = $value['cost_price'];
                    $stPrice[$i] = $value['standard_price'];
                    $spPrice[$i] = $value['special_price'];
                    $ids[$i] = $value['ids'];
                    $i++;
                }

                $this->productManagerModel->updatePrice($ids, $price, $costPrice, $stPrice, $spPrice);
                $this->handleSavePriceForm();
                $this->flashMessage('Změny byly uloženy.', 'success');
                $this->redirect('Product:price');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };   
        
        return $form;
    }
    
    public function createComponentAddStockForm() {
        
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
        }
        
        $form = $this->productForm->addStock();
            
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {   
                if($this->productManagerModel->productExists($values->id)){
                    $this->productManagerModel->addStock(
                            $values->id,
                            $values->quantity
                            );
                    $this->flashMessage('Naskladnění proběhlo úspěšně.', 'success');
                    $this->redirect('Product:stock');
                }else{
                    $this->flashMessage('Produkt s tímto ID neexistuje.', 'fail');
                    $this->redirect('Product:stock');
                }
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
       
    public function actionDeleteProduct($id){
        $p = $this->productManagerModel->deleteProduct($id);
        if(!$p){
            $this->flashMessage('Nelze smazat.', 'fail');
            $this->redirect('Product:default');
        }
        $this->flashMessage('Produkt byl smazaný', 'success');
        $this->redirect('Product:default');
    }
    
    public function actionDeleteProductCategory($id){
        
        $products = $this->productManagerModel->getProductsCategory();
        
        $i=0;
        $ids[] = null;
        $toDelete[] = null;
        
        foreach($products as $product){
            if($product->product_category_id == $id){
                $ids[$i] = $product->id;
                $toDelete[$i] = 0;
                $i++;
            }
        }
        
        $this->productManagerModel->updateCategory($ids, $toDelete);
        $p = $this->productManagerModel->productCategoryModel->deleteCategory($id);
        
        if(!$p){
            $this->flashMessage('Nelze smazat.', 'fail');
            $this->redirect('Product:category');
        }
        $this->flashMessage('Kategorie byla smazaná.', 'success');
        $this->redirect('Product:category');
    }
    

    
}
