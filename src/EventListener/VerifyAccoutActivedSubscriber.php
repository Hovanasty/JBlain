<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VerifyAccoutActivedSubscriber  implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var SessionInterface
     */
    private $session;



    public function __construct(SessionInterface $session, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager)
    {

        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if ($token = $this->tokenStorage->getToken()) {

            if (is_object($user = $token->getUser())) {
                // e.g. anonymous authentication

                if ($user->getAccountActivationToken()) {



                    $event->setController(function () {

                        //$this->get('session')->set('user', null);
                        //$this->session->set(null);

                        $response = new Response();
                        $response->headers->clearCookie('REMEMBERME');
                        $response->send();

                        $this->tokenStorage->setToken(null);

                        $this->session->getFlashBag()->add('danger', 'veuillez activer votre compte pour vous connecter.');
                        return new RedirectResponse('/login');

                    });
                }
            }
        }
    }
}
