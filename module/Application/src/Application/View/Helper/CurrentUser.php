<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
class CurrentUser extends AbstractHelper
{
    public function __invoke()
    {
        $authService = new \Zend\Authentication\AuthenticationService;
        return $authService->getStorage()->read()['user'];
    }
}