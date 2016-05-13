<?php

namespace Application\Controller;

use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;

abstract class AbstractController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $_em;

    /**
     * @var \Application\Service\AuthService
     */
    protected $authService;

    /**
     * Returns the EntityManager
     *
     * Fetches the EntityManager from ServiceLocator if it has not been initiated
     * and then returns it
     *
     * @access protected
     * @return EntityManager
     */
    protected function em()
    {
        if (null === $this->_em)
            $this->_em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        return $this->_em;
    }

    /**
     * @return \Application\Service\AuthService
     */
    public function getAuthService()
    {
        if (! $this->authService)
            $this->authService = $this->getServiceLocator()->get('auth_service');

        return $this->authService;
    }

    /**
     * @return \Zend\Authentication\Storage\StorageInterface
     */
    protected function session()
    {
        return $this->getAuthService()->getStorage();
    }

    /**
     * @return \Application\Entity\User
     */
    protected function currentUser()
    {
        $user = $this->session()->read()['user'];
        $uid = ($user)? $user->getId() : 0; // TODO: something wrong here
        return $this->em()->find('\Application\Entity\User', $uid);
    }

    public function checkIfLoggedIn()
    {
        $user = $this->currentUser();
        if (is_null($user))
        {
            $this->session()->write(['return_url' => urlencode($this->getRequest()->getUri())]);
            return $this->redirect()->toRoute('auth', ['action' => 'login']);
        }
        return true;
    }

    public function checkIfOwner($uid)
    {
        $result = $this->checkIfLoggedIn();
        if ($result instanceof \Zend\Http\Response)
            return $result;

        $user = $this->currentUser();
        $role = (is_null($user->getRole()))? 1 : $user->getRole()->getId();
        if ($uid == $user->getId() || $role == 2) // 2 hardcoded to admin
            return true;
        else
        {
            $this->flashMessenger()->addErrorMessage('Not allowed');
            return $this->redirect()->toRoute('home');
        }
    }

    public function jsonOutput($data)
    {
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaders(['Content-Type' => 'application/json']);
        $response->setContent(json_encode($data));
        return $response;
    }
}