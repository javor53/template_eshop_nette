<?php

namespace AdminModule;

use Nette;
use App\Model;
use App\Forms;
use Nette\Application\UI\Form;
use Nette\Utils\Image;
use Nette\Utils\FileSystem;

class EventPresenter extends BasePresenter {

    /** @var Model\EventModel @inject */
    public $eventModel;
    
    /** @var Forms\ImageUploadForm @inject */
    public $imageUploadForm;

    /** @var Forms\EventForm @inject */
    public $eventForm;
    
    public function renderDefault() {
        
    }
    public function renderDetail($id) {
        $event = $this->eventModel->getEventById($id);
        $this->template->event = $event;
        $this->template->image = $this->eventModel->getImageById($event->image_id);
    }
    public function renderAdminShow() {
        $this->template->events = $this->eventModel->getEvents();
        $this->template->images = $this->eventModel->getImages();
    }
    
    public function createComponentEventForm() {
        $form = $this->eventForm->create();
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $file = $values['file'];                
                $file_ext = strtolower(mb_substr($file->getSanitizedName(), strrpos($file->getSanitizedName(), ".")));
                $file_name = $values->title;

                if($this->eventModel->imgExists($file_name . $file_ext)){

                    $this->flashMessage('Chyba: Zvolte jiný název obrázku.', 'success');
                    $this->redirect('this');
                    return;
                }

                $file->move('img/events_img/' . $file_name . $file_ext);
                $path = "img/events_img/";
                $img = Image::fromFile('img/events_img/' . $values->title . $file_ext);

                //$img->save('img/' . $values->title . '_full' . $file_ext);
                // $img->resize(300, null);
                $img->save('img/events_img/' . $file_name . $file_ext);

                $imgId = $this->eventModel->uploadImage(
                        $path,
                        $file_name,
                        $file_ext
                        );
                
                
                
                $this->eventModel->createEvent(
                        $values->title,
                        $values->description,
                        $values->date,
                        $values->place,
                        $imgId
                        );
                $this->redirect(':Admin:Event:adminShow');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }
    
    public function createComponentEditEventForm() {
        $form = $this->eventForm->createEditForm();
        
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                

                $this->eventModel->editEvent(
                        $values->id,
                        $values->title,
                        $values->description,
                        $values->date,
                        $values->place
                        );
                $this->redirect(':Admin:Event:adminShow');
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
        
    }
    
    public function actionDelete($id) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        
        $event = $this->eventModel->getEventById($id);
        if($event->image_id != NULL){
            FileSystem::delete($this->eventModel->getUrlImg($event->image_id));
            $this->eventModel->deleteImg($event->image_id);
        }
        //FileSystem::delete($this->imagesModel->getUrlImg($id+1));
        $this->eventModel->deleteEvent($id);
        
        //$this->imagesModel->deleteImg($id+1);
        
        //$this->imageDescModel->deleteDesc($file);
        
        $this->flashMessage('Událost byla úspěšně smazaná', 'success');
        $this->redirect('Event:adminShow');
    }
    
    public function actionEdit($id) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
        $event = $this->eventModel->getEventById($id);
         

        if (!$event) {
            $this->error('Událost nebyla nalezena.');
        }
        
        $this['editEventForm']->setDefaults(array(
            'id' => $event->id,
            'title' => $event->title,    
            'date' => $event->date,
            'place' => $event->place,
            'description' => $event->description,
        ));
    }
}
    