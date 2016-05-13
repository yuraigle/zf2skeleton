<?php

namespace Application\Service;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class AuthService extends \Zend\Authentication\AuthenticationService implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var EntityManager
     */
    protected $_em;

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return AuthService
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

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
            $this->_em = $this->getServiceManager()->get('Doctrine\ORM\EntityManager');

        return $this->_em;
    }

    /**
     * @param $request
     * @return array of validation messages
     */
    protected function validateRegisterRequest($request)
    {
        $messages = array();

        // email validations
        $emailNotEmpty = new \Zend\Validator\NotEmpty();
        $emailNotEmpty->setMessage(
            'Email cannot be empty',
            \Zend\Validator\NotEmpty::IS_EMPTY
        );

        $emailValidEmail = new \Zend\Validator\EmailAddress();
        $emailValidEmail->setMessage(
            'User email is not a valid email address. Use the basic format local-part@hostname',
            \Zend\Validator\EmailAddress::INVALID_FORMAT
        );

        $emailChain = new \Zend\Validator\ValidatorChain();
        $emailChain
            ->attach($emailValidEmail);

        // is unique
        $user = $this->em()->getRepository('\Application\Entity\User')->findBy(
            array('email' => $request['email']));
        if (count($user))
            $messages[] = "User with this email already exists";

        // password validations
        $passwordNotEmpty = new \Zend\Validator\NotEmpty();
        $passwordNotEmpty->setMessage(
            "User password cannot be empty",
            \Zend\Validator\NotEmpty::IS_EMPTY
        );

        $passwordStringLength = new \Zend\Validator\StringLength(['min'=>4, 'max'=>20]);
        $passwordStringLength->setMessage(
            "User password is less than %min% characters long",
            \Zend\Validator\StringLength::TOO_SHORT
        );
        $passwordStringLength->setMessage(
            "User password is more than %max% characters long",
            \Zend\Validator\StringLength::TOO_LONG
        );

        $passwordChain = new \Zend\Validator\ValidatorChain();
        $passwordChain
            ->attach($passwordNotEmpty)
            ->attach($passwordStringLength);


        if (! $passwordChain->isValid($request['password']))
            $messages = array_merge($messages, $passwordChain->getMessages());
        if (! $emailChain->isValid($request['email']))
            $messages = array_merge($messages, $emailChain->getMessages());

        return $messages;
    }

    /**
     * Create a new user
     *
     * @param $request
     * @return \Application\Entity\User
     */
    public function register($request)
    {
        $messages = $this->validateRegisterRequest($request);

        if (empty($messages))
        {
            $salt = md5(time());
            $password_md5 = md5($salt . $request['password']);

            $user = new \Application\Entity\User();
            $user->setEmail($request['email']);
            $user->setSalt($salt);
            $user->setPassword($password_md5);

            $this->em()->persist($user);
            $this->em()->flush();

            $status = true;
            $entity = $user;
        }
        else
        {
            $status = false;
            $entity = null;
        }

        return array(
            "status" => $status,
            "messages" => $messages,
            "entity" => $entity,
        );
    }

    /**
     * @param $request
     * @return \Zend\Authentication\Result
     */
    public function login($request)
    {
        $adapter = new \DoctrineModule\Authentication\Adapter\ObjectRepository(array(
            'objectManager' => $this->em(),
            'identityClass' => 'Application\Entity\User',
            'identityProperty' => 'email',
            'credentialProperty' => 'password',
            'credentialCallable' => 'Application\Entity\User::hashPassword'
        ));
        $adapter
            ->setIdentity($request['email'])
            ->setCredential($request['password']);

        return $adapter->authenticate();
    }

    /**
     * @param $request
     * @return array of validation errors
     */
    protected function validateRememberRequest($request)
    {
        $messages = array();

        $user = $this->em()->getRepository('\Application\Entity\User')->findBy(
            array('email' => $request['email']));
        if (count($user) != 1)
            $messages[] = "A record with the supplied identity could not be found.";

        return $messages;
    }

    /**
     * send a password reset letter
     *
     * @param $request
     * @return array
     */
    public function sendRememberPasswordInstructions($request)
    {
        $messages = $this->validateRememberRequest($request);

        if (empty($messages))
        {
            $entity = $this->em()->getRepository('Application\Entity\User')
                ->findOneBy(array("email" =>$request['email']));

            $entity->setResetCode(md5(time()));
            $entity->setResetAskedAt(new \DateTime());
            $this->em()->persist($entity);
            $this->em()->flush();

            //todo: send a letter

            $status = true;
        }
        else
        {
            $status = false;
            $entity = null;
        }

        return array(
            "status" => $status,
            "messages" => $messages,
            "entity" => $entity,
        );
    }
}
