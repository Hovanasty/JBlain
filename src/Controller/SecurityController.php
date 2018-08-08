<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use App\Services\Mail\Mailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
                $activationTokenAccount = uniqid();
                $urlActivationAccount = $this->generateUrl('activation_account', array('activationTokenAccount' => $activationTokenAccount), UrlGeneratorInterface::ABSOLUTE_URL);
                $user->setPassword($password);
                $user->setAccountActivationToken($activationTokenAccount);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $mailer->sendActivationAcountMail($urlActivationAccount,$user,$user->getEmail());

                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $this->container->get('session')->set('_security_main', serialize($token));

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
     * @Route("/activation_account/{token}" name="activation_account")
     *
     */
    public function ActivationAccount(Request $request, $token)
    {
        /*
        dump($token);
        $userSession = $this->getDoctrine()-> getRepository('App:User')->findOneBy(['accountActivationToken' => $token]);
        dump($userSession);die;

        $userPasswordLost = $this->getDoctrine()->getRepository(User:: class)->findOneBy(['email' => $email]);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $this->container->get('session')->set('_security_main', serialize($token));
        */
    }

}
