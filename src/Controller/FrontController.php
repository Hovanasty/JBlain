<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Form\VerifyUserType;
use App\Services\Mail\Mailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class FrontController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }



    /**
     * @Route("/forgot_password", name="forgotpassword")
     */
    public function forgotPassword(Request $request, Mailer $mailer)
    {

        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $email = $data['email'];

            $userPasswordLost = $this->getDoctrine()
                ->getRepository(User:: class)
                ->findOneBy([
                    'email' => $email
                ]);

            if ($userPasswordLost) {
                $token = uniqid();
                $userPasswordLost->setToken($token);
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $url = $this->generateUrl('resetPassword', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);
                // UrlGeneratorInterface::ABSOLUTE_URL

                $to = $userPasswordLost->getEmail();
                $mailer->sendForgotPasswordMail($url, $userPasswordLost, $to);

                $this->addFlash('success', 'Consultez votre boite mail. Un message vous a été envoyé avec un lien pour réinitialiser votre mot de passe  ');
            } else {

                $this->addFlash('error', 'Nous n\'avons pas trouvé d\'utilisateur avec cet email, merci de rééssayer');


                return $this->redirectToRoute('forgotpassword');
            }
        }
        return $this->render('user/forgot_password.html.twig', array(
            'form'=>$form->createView()
        ));
    }

    /**
     * @Route("/resetpassword/{token}", name="resetPassword")
     * @Method({"GET", "POST"})
     *
     */
    public function resetPassword ($token, Request $request, UserPasswordEncoderInterface $encoder)
    {


        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newPassword = $data['newPassword'];
            $user = $this->getDoctrine()->getRepository(User:: class)->findOneBy(['token' => $token]);

            if ($user) {

                $entityManager = $this->getDoctrine()->getManager();

                $passwordEncoded = $encoder->encodePassword($user, $newPassword);
                $user->setPassword($passwordEncoded);

                $user->setToken(null);


                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été mis à jour');

                return $this->redirectToRoute('homepage');

            } else {
                $this->addFlash('error', 'la réinitialisation de votre mot de passe a échoué, veuillez renouveler votre demande');

                return $this->redirectToRoute('forgotpassword');
            }
        }
        return $this->render('user/reset_password.html.twig', array(
            'form'=>$form->createView()

        ));
    }

    /**
     * @Route("mentions_legales", name="mentionsLegales")
     *
     */
    public function legalsMentionShow()
    {
        return $this->render('front/mentionsLegales.html.twig');
    }

    /**
     * @Route("send_email_account_activation", name="sendEmailAccountActivation")
     *
     */
    public function sendEmailAccountActivation(Request $request, UserPasswordEncoderInterface $passwordEncoder, Mailer $mailer)
    {
        $form = $this->createForm(VerifyUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $email = $data['email'];
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->findOneBy(['email'=> $email]);
            $verifyUser = $passwordEncoder->isPasswordValid($user,$data['plainPassword']);
            $token = $user->getAccountActivationToken();


            if (($verifyUser == true) && $token) {

                // on flush le token d'activation de compte
                $accountTokenActivation = uniqid();
                $urlAccountActivation = $this->generateUrl(
                    'account_activation',
                    array('accountTokenActivation' => $accountTokenActivation),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $user->setAccountActivationToken($accountTokenActivation);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // on prévient via un mail l'utilisateur qu'il doit activer son compte
                $mailer->sendActivationAcountMail($urlAccountActivation, $user, $user->getEmail());

                $this->addFlash('info', 'Un mail d\'activation vous a été envoyé');

                return $this->redirectToRoute('homepage');

            } elseif (($verifyUser == false) || $token){
                $this->addFlash('error', 'mot de passe ou email incorrect');

            } elseif (($verifyUser == true) && !$token){
                $this->addFlash('error', 'compte déjà activé');

            }
        }

        return $this->render('front/VerifyUserTypeSendActivationAcount.html.twig', array(
            'form'=>$form->createView())
        );
    }

}
