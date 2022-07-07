<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegistrationController extends AbstractController
{

    private $loginAuthenticator;
    private $guard;
    private $mailerService;
    private $emailSubject = 'Confirmation de votre inscription';
    private $templateEmail = 'emails/register_confirm.html.twig';

    public function __construct(
        LoginFormAuthenticator $loginAuthenticator,
        GuardAuthenticatorHandler $guard,
        MailerService $mailerService
    )
    {
        $this->loginAuthenticator = $loginAuthenticator;
        $this->guard = $guard;
        $this->mailerService = $mailerService;
    }

    /**
     * @Route("/register", name="app_register", methods={"POST"})
     */
    public function register(AuthenticationUtils $authenticationUtils,Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user,['attr' => ['urlAction' => $this->generateUrl('app_register')]]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setUsername($user->getEmail());
            $user->setRoles(['ROLE_USER']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $this->guard->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $this->loginAuthenticator,
                'main'
            );
            $this->mailerService->sendTemplatedEmail($user->getEmail(),$this->emailSubject, $user->getEmail(), $this->templateEmail);
            return $this->redirectToRoute('home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername= $authenticationUtils->getLastUsername();

        return $this->render('connexion/login.html.twig', [
            'registrationForm' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }
}
