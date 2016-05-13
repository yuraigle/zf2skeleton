<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;

class IndexController extends \Admin\Controller\AbstractController
{
    public function indexAction()
    {
        return new ViewModel([
            'active_tab' => 'home'
        ]);
    }
}