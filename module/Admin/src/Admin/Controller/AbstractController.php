<?php

namespace Admin\Controller;

use Zend\Mvc\MvcEvent;

abstract class AbstractController extends \Application\Controller\AbstractController
{
    public function checkAdminRights()
    {
        $user = $this->currentUser();
        if (! $user)
        {
            $this->session()->write(['return_url' => urlencode($this->getRequest()->getUri())]);
            return $this->redirect()->toRoute('auth', ['action' => 'login']);
        }
        elseif ($user->getRole() != "admin")
        {
            $this->flashMessenger()->addErrorMessage('Not allowed');
            return $this->redirect()->toRoute('home');
        }

        return true;
    }

    public function onDispatch(MvcEvent $e)
    {
        $result = $this->checkAdminRights();
        if ($result instanceof Response)
            return $result;

        $this->layout('layout/admin');
        return parent::onDispatch($e);
    }
}