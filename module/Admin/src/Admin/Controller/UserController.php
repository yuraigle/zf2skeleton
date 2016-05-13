<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;

class UserController extends \Admin\Controller\AbstractController
{
    public function indexAction()
    {
        $limit = 25;
        $page = $this->params('page', 1);

        $cond = "1 = 1";
        $query_count = $this->em()->createQuery("select count(u) from \Application\Entity\User u where $cond order by u.createdAt DESC");
        $query = $this->em()->createQuery("select u from \Application\Entity\User u where $cond order by u.createdAt DESC")
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        // create paginator control (null adapter)
        $count = $query_count->getSingleScalarResult();
        $paginator = new \Zend\Paginator\Paginator(
            new \Zend\Paginator\Adapter\Null($count)
        );
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);

        return new ViewModel([
            "active_tab" => "user",
            "paginator" => $paginator,
            "items" => $query->getResult(),
        ]);
    }

    public function deleteAction()
    {
        $id = $this->params()->fromPost('id');
        $format = $this->params()->fromPost('format');

        if ($this->getRequest()->isPost() && $format == 'json')
        {
            $this->flashMessenger()->addInfoMessage('Nothing done. Fake.');
            return $this->jsonOutput(['status'=>"OK"]);
        }

        $this->layout('layout/empty');
        return new ViewModel(["id" => $id]);
    }
}