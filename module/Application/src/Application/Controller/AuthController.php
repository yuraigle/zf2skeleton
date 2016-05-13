<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;

class AuthController extends \Application\Controller\AbstractController
{
    public function logoutAction()
    {
        // if is not logged in, don't need to log out
        if (! $this->currentUser())
            return $this->redirect()->toRoute('home');

        $this->session()->clear();
        $this->flashMessenger()->addInfoMessage("Logged out");
        return $this->redirect()->toRoute('home');
    }

    public function loginAction()
    {
        // if is logged in, don't need to register
        if ($this->currentUser())
        {
            $this->flashMessenger()->addInfoMessage("Already signed in");
            return $this->redirect()->toRoute('home');
        }

        $request = $this->params()->fromPost('user');

        if ($this->getRequest()->isPost())
        {
            $result = $this->getAuthService()->login($request);

            if ($result->isValid())
            {
                $this->session()->write(array("user" => $result->getIdentity()));
                $this->flashMessenger()->addSuccessMessage("Authentication successful");
                return $this->redirect()->toRoute('home');
            }
            else
            {
                foreach ($result->getMessages() as $message)
                    $this->flashMessenger()->addErrorMessage($message);
            }
        }

        $this->layout('layout/simple');
        return new ViewModel(
            array(
                "user" => $request,
            )
        );
    }

    public function registerAction()
    {
        // if is logged in, don't need to register
        if ($this->currentUser())
        {
            $this->flashMessenger()->addInfoMessage("Already signed in");
            return $this->redirect()->toRoute('home');
        }

        $request = $this->params()->fromPost('user');

        if ($this->getRequest()->isPost())
        {
            $result = $this->getAuthService()->register($request);
            if ($result['status'] === true)
            {
                $this->session()->write(array("user" => $result['entity']));
                $this->flashMessenger()->addSuccessMessage("Registration successful");
                return $this->redirect()->toRoute('home');
            }
            else
            {
                foreach ($result['messages'] as $m)
                    $this->flashMessenger()->addErrorMessage($m);
            }
        }

        $this->layout('layout/simple');
        return new ViewModel(
            array(
                "user" => $request,
            )
        );
    }

    public function rememberAction()
    {

        $request = $this->params()->fromPost('user');

        if ($this->getRequest()->isPost())
        {
            $result = $this->getAuthService()->sendRememberPasswordInstructions($request);
            if ($result['status'] === true)
            {
                $this->flashMessenger()->addInfoMessage("A letter with password reset instructions sent");
                return $this->redirect()->toRoute('home');
            }
            else
            {
                foreach ($result['messages'] as $message)
                    $this->flashMessenger()->addErrorMessage($message);
            }
        }

        $this->layout('layout/simple');
        return new ViewModel(
            array("user" => $request)
        );
    }

    public function resetAction()
    {
        if ($this->getRequest()->isPost())
        {
            // TODO: Reset password here
        }

        return new ViewModel();
    }

    public function termsAction()
    {
        return new ViewModel();
    }
}