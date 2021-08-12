<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\ResetPassType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Monolog\Handler\SwiftMailerHandler;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("/profil", name="profil")
     * @return Response
     */
    public function profil()
    {
        return $this->render('security/profil.html.twig');
    }

    /**
     * @Route("/inscription", name="security_registration")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @param Mailer $mailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, MailerInterface $mailer)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);

            // on genere le token d'activation
            $user->setActivationToken(md5(uniqid()));

            $manager->persist($user);
            $manager->flush();
            $email = (new Email())
                ->from('hello@example.com')
                ->to($user->getEmail())
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject('Time for Symfony Mailer!')
                ->text('Sending emails is fun again!')
                ->html($this->renderView('emails/activation.html.twig', ['token' => $user->getActivationToken()]));
            $mailer->send($email);


            return $this->redirectToRoute('security_login');
        }
        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login()
    {
        return $this->render('security/index.html.twig');
    }

    /**
     * @Route("/logout", name="security_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }

    /**
     * @Route ("/activation/{token}", name="activation")
     * @param $token
     * @param UserRepository $repository
     * @return RedirectResponse
     */
    public function activation($token, UserRepository $repository)
    {
        // on verifie si un user a ce token
        $user = $repository->findOneBy(['activation_token' => $token]);

        // si aucun user exist avec ce token
        if (!$user){
            // erreur 404
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }
        // on supprime le token
        $user->setActivationToken(null);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();
        // on envoi un message flash
        $this->addFlash('message', 'vous avez activé votre compte');
        // on retourne a l'acceuil
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/oubli-pass", name="app_forgot_password")
     * @param Request $request
     * @param UserRepository $repository
     * @param MailerInterface $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @return RedirectResponse
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function forgotPassword(Request $request, UserRepository $repository, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $form = $this->createForm(ResetPassType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();

            $user =$repository->findOneByEmail($data['email']);

            if (!$user){
                $this->addFlash('danger', 'cette adresse existe pas');
                $this->redirectToRoute('security_login');
            }
            $token = $tokenGenerator->generateToken();

            try {
                $user->setResetToken($token);
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($user);
                $manager->flush();
            }catch (\Exception $exception){
                $this->addFlash('warning', 'erreur marche pas : '.$exception->getMessage());
                return  $this->redirectToRoute('security_login');
            }
            $url = $this->generateUrl('app_reset_password', ['token' => $token]);

            $email = (new Email())
                ->from('hello@example.com')
                ->to($user->getEmail())
                ->subject('mot de pass oublié')
                ->text('Sending emails is fun again!')
                ->html('clique sur le lien' . $url);
            $mailer->send($email);

        }
    }

}
