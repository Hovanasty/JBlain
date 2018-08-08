<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use App\Services\Mail\Mailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="user_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, Mailer $mailer)
    {
        if ($this->getUser()){
            return $this->redirectToRoute('logout');
        }else{
            $user = new User();
            $form = $this->createForm(UserType::class, $user);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $accountTokenActivation = uniqid();
                $urlAccountActivation = $this->generateUrl(
                    'account_activation',
                    array('accountTokenActivation' => $accountTokenActivation),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $user->setPassword($password);
                $user->setAccountActivationToken($accountTokenActivation);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $mailer->sendActivationAcountMail($urlAccountActivation,$user,$user->getEmail());

                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $this->container->get('session')->set('_security_main', serialize($token));

                $this->addFlash('success', 'Compte créé');
                $this->addFlash('info', 'Un mail d\'activation vous a été envoyé');

                return $this->redirectToRoute('homepage');
            }

            return $this->render(
                'security/register.html.twig',
                array('form' => $form->createView())
            );
        }

    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        if ($this->getUser()){
            return $this->redirectToRoute('logout');
        }else{
            $error = $authenticationUtils->getLastAuthenticationError();

            $lastUsername = $authenticationUtils->getLastUsername();

            return $this->render('security/login.html.twig', array(
                'last_username' => $lastUsername,
                'error'         => $error,
            ));
        }

    }

    /**
     * @Route("/account_activation/{accountTokenActivation}", name="account_activation")
     */
    public function ActivationAccount(Request $request, $accountTokenActivation)
    {
        $user = $this->getDoctrine()-> getRepository('App:User')->findOneBy(['accountActivationToken' => $accountTokenActivation]);

        if (is_object($user)){

            $user->setAccountActivationToken(Null);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $this->container->get('session')->set('_security_main', serialize($token));

            $this->addFlash('success', 'Compte activé !');

        }else{

            $this->addFlash('error', 'Aucun compte à activer');
        }

        return $this->render('front/index.html.twig');
    }
}
