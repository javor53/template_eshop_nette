<?php

namespace App\Forms;

use Nette;
use Nette\Model;
use Nette\Application\UI\Form;

class CategoryForm implements IForm
{
        
    public function __construct(\App\Model\ArticleCategoryModel $articleCategoryModel) {
        $this->articleCategoryModel = $articleCategoryModel;
    }
    
    
    
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();

        $form->addText('title')->setRequired();
        $form->addText('description');
        $form->addSubmit('save');
        
        return $form;
    }

    public function createEditForm()
    {
        $form = new Form();

        $form->addText('id')->setRequired();
        $form->addText('title')->setRequired();
        $form->addText('description');
        $form->addSubmit('save');

        return $form;
    }
    public function createPostCategoryEditForm()
    {
        $form = new Form();

        $categories = $this->articleCategoryModel->getCategoryTitles();
        
        $form->addText('id')->setRequired();
        $form->addSelect('category')->setItems($categories, false);
        $form->addSubmit('save');

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