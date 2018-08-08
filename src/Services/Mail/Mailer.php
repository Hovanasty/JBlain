<?php
namespace App\Services\Mail;



class Mailer
{

    private $adminEmail;
    protected $environment;
    protected $mailer;

    public function __construct($adminEmail, \Twig_Environment $environment, \Swift_Mailer $mailer)
    {
        $this->adminEmail = $adminEmail;
        $this->environment = $environment;
        $this->mailer = $mailer;
    }

    protected function sendMail($subject, $body, $to)
    {
        $mail = new \Swift_Message();

        $mail
            ->setFrom($this->adminEmail)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setContentType('text/html');

        $this->mailer->send($mail);
    }

    public function sendForgotPasswordMail($url, $userPasswordLost, $to)
    {
        $subject = 'mot de passe perdu';

        $body = $this->environment->render('mail/forgotPasswordMail.html.twig', array(
            'user' => $userPasswordLost,
            'url' => $url,
        ));
        $this->sendMail($subject, $body, $to);
    }

    public function sendActivationAcountMail($url, $user, $to)
    {
        $subject = 'Activation de compte';

        $body = $this->environment->render('mail/activationAcountMail.html.twig',array(
            'user' => $user,
            'url' => $url,
        ));
        $this->sendMail($subject, $body, $to);
    }

    /*
    protected $mailer;
    protected $templating;
    private $from = 'romain.poilpret@gmail.com';
    private $to = 'romain.poilpret@gmail.com';



    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    protected function sendMail($subject, $body, $to)
    {
        $mail = \Swift_Message::newInstance();

        $mail
            ->setFrom($this->from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setContentType('text/html');

        $this->mailer->send($mail);
    }

    public function sendContactMail($message, $reply)
    {

        $to = $this->to;
        $subject = 'Demande de contact';
        $body = $this->templating->render('Mail/contactMail.html.twig', array(
            'message' => $message
        ));
        $this->sendMail($subject, $body, $to, $reply);
    }

    public function sendForgotPasswordMail($to, $subject, $url, $userPasswordLost)
    {
        $body = $this->templating->render('Mail/forgotPasswordMail.html.twig', array(
            'user' => $userPasswordLost,
            'subject' => $subject,
            'url' => $url,
        ));
        $this->sendMail($subject, $body, $to);
    }
    */
}
