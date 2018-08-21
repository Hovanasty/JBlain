<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;
use App\Form\RemoveUserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends Controller
{
    /**
     * @Route("/profil", name="profil")
     */
    public function index()
    {
        return $this->render('user/profil.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }


    /**
     * @Route("/delete_user", name="deleteUser")
     */
    public function deleteUser(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();

        $form = $this->createForm(RemoveUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            if ($passwordEncoder->isPasswordValid($user, $user->getPlainPassword())) {


                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($user);
                $entityManager->flush();



                // vide la session afin de ne pas la recharger avec un profil supprimé
                $token = null;
                $this->container->get('security.token_storage')->setToken($token);

                $this->addFlash('success', 'Votre compte est supprimé');



                return $this->redirectToRoute('logout');
            }
        }


        return $this->render('user/deleteUser', array('form'=>$form->createView()));

    }

    /**
     * @Route("/send_another_activation_email", name="sendAnotherActivationEmail")
     */
    public function sendAnotherActivationEmail()
    {
        $user = $this->getUser();
        dump($user);die;
    }
}
