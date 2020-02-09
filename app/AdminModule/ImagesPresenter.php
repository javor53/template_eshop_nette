<?php

namespace AdminModule;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Utils\Image;
use Nette\Utils\FileSystem;

class ImagesPresenter extends BasePresenter {

    /** @var Model\ArticleModel @inject */
    public $articleModel;
    
    /** @var Model\ImagesModel @inject */
    public $imagesModel;
    
    /** @var Model\ImageDescriptionModel @inject */
    public $imageDescModel;
    
    
    /** @var Model\PostImagesModel @inject */
    private $postImagesModel;

  
    /** @var Model\CollectionsModel @inject */
    public $collectionsModel;
    
    public function handleSort($type){
        $images = $this->imagesModel->getAll($type);
        
        $this->template->images = $images;
        if ($this->isAjax()) {
            $this->redrawControl('sortImages');
        }else {
            $this->redirect('this');
        }
    }
    
    public function renderDefault($collection) {
        if($collection == NULL){
            $collection = $this->imagesModel->getColl(1);//select default collection
        }
        
        $col = $this->collectionsModel->getCollections();
        $image = $this->imagesModel->getByCollection($collection);
        
        //if (!$image) {
          //  $this->error('Stranka nebyla nalezena');
        //}

        $this->template->images = $image;
        $this->template->collections = $col;
        
    }
    
    public function renderShow($id){
        
        $image = $this->imagesModel->getImg($id);
        $desc = $this->imageDescModel->getDescription($id);
        
        
        $this->template->image = $image; 
        $this->template->desc = $desc;
    }
    public function renderUpload(){
        $images = $this->imagesModel->getAll()->order('id DESC');
        $collections = $this->collectionsModel->getCollections();
        
        $this->template->collections = $collections;
        
        if (!isset($this->template->images)) {
            $this->template->images = $images;
        }
        if ($this->isAjax()) {
            $this->redrawControl('sortImages');
        }
    }
    



    public function createComponentImgForm() {
        $form = new Form;
        $form->addUpload('file', 'Soubor:')
        ->setRequired();
        $form->addText('title', 'Název:')
        ->setRequired();
        $form->addSelect('menu', 'Kategorie', $this->imagesModel->getList());
        
        $form->addText('descTitle', 'Nadpis:');
        $form->addText('description', 'Text:');
        
        $form->addSubmit('Upload');
        
        $form->onSuccess[] = [$this, 'imgFormSucceeded'];
        return $form;
    }

    public function imgFormSucceeded($form) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pro nahrání obrázku se musíte přihlásit.');
        }

        $postId = $this->getParameter('postId');

        $values = $form->getValues();
        $file = $values['file'];
        // kontrola jestli se jedná o obrázek a jestli se nahrál dobře
        // 
        // oddělení přípony pro účel změnit název souboru na co chceš se zachováním přípony
        $file_ext = strtolower(mb_substr($file->getSanitizedName(), strrpos($file->getSanitizedName(), ".")));
        // vygenerování náhodného řetězce znaků, můžeš použít i \Nette\Strings::random()
        $file_collection = $values->menu;

        
        $file_name = $values->title;
        
        
        if($this->imagesModel->ImgExists($file_name . $file_ext)){

            $this->flashMessage('Chyba: Zvolte jiný název obrázku.', 'success');
            $this->redirect('this');
            return;
        }
        
        // přesunutí souboru z temp složky někam, kam nahráváš soubory
        $file->move('img/' . $file_name . $file_ext);
        $path = "img/";

        $img = Image::fromFile('img/' . $values->title . $file_ext);
        
        //$img->save('img/' . $values->title . '_full' . $file_ext);
        // $img->resize(300, null);
        
        $img->save('img/' . $file_name . $file_ext);
        
        $arrForm = [$postId,$path,$file_name,$file_ext,$file_collection];
        
        $id = $this->imagesModel->insert($arrForm);
        //uložení druhého img v plné velikosti + vložení popisu
        /*$lastId = $id->id; 
        
        $this->imageDescModel->insertImgDescription([$lastId,$values->descTitle,$values->description,$lastId]);
        
        $arrForm[2] = $values->title . '_full';
        $this->imagesModel->insert($arrForm);*/

        $this->flashMessage('Obrazek byl úspěšně nahraný', 'success');
        $this->redirect('this');
    }

    
    
    
    public function actionDelete($id) {

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $file = $this->imagesModel->getImg($id);

        FileSystem::delete($this->imagesModel->getUrlImg($id));
        //FileSystem::delete($this->imagesModel->getUrlImg($id+1));
        $this->imagesModel->deleteImg($id);
        //$this->imagesModel->deleteImg($id+1);
        
        //$this->imageDescModel->deleteDesc($file);
        
        $this->flashMessage('Obrazek byl úspěšně smazaný', 'success');
        $this->redirect('Images:upload');
    }

}
