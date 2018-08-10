<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RemoveUserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class TestController extends Controller
{
    /**
     * @Route("/test", name="test")
     */
    public function index()
    {
        $var1 = $_SERVER;
        $var2 = $_ENV;
        $var3 = $GLOBALS;
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
            'server' => $var1,
            'env' => $var2,
            'session' => $var3
        ]);
    }

    /**
     * @Route("/testDelete", name="test_delete")
     */
    public function test(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();

        $form = $this->createForm(RemoveUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            if ($passwordEncoder->isPasswordValid($user, $user->getPlainPassword())) {


                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($user);
                $entityManager->flush();

                $token = null;
                $this->container->get('security.token_storage')->setToken($token);

                $this->addFlash('success', 'Votre compte est supprimé');



                return $this->redirectToRoute('logout');
            }
        }


        return $this->render('test/deleteTest.html.twig', array('form'=>$form->createView()));

    }

}
