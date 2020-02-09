<?php

namespace App\Forms;

use Nette;

interface IForm
{
    /**
     * @return Nette\Application\UI\Form
     */
    public function create();

    /**
     * @param Nette\Application\UI\Form $form
     * @param $values
     * @return mixed
     */
    public function formSucceeded(Nette\Application\UI\Form $form, $values);
}