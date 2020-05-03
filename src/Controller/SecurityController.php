<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route(name="login", path="/login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @Route("/reset_password_ask", name="app_ask_reset_password")
     */
    public function askResetPassword()
    {
        return $this->render('security/ask_reset_password.html.twig');
    }

    /**
     * @Route("/forgotten_password", name="app_forgotten_password", methods={"POST"})
     */
    public function resetForgottenPassword(EntityManagerInterface $entityManager, Request $request, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator): Response
    {
        $email = $request->request->get('email');
        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($email === null) {
            $this->addFlash('error', 'Email unknown');
            return $this->redirectToRoute('admin');
        }
        $token = $tokenGenerator->generateToken();

        try {
            $user->setResetToken($token);
            $entityManager->flush();
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('admin');
        }

        $url = $this->generateUrl('app_reset_password', [
            'token' => $token
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = (new \Swift_Message('Forgot Password'))
            ->setFrom('siteweb@alteragile.com')
            ->setTo($user->getEmail())
            ->setBody("use link below to reset password : " .
                $url, 'text/html');
        $mailer->send($message);

        $this->addFlash('notice', 'Email sent');

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route(path="/reset_password/{token}", name="app_reset_password", methods={"POST", "GET"})
     */
    public function resetPassword(EntityManagerInterface $entityManager, Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($request->isMethod('POST')) {
            /** @var User $user */
            $user = $entityManager->getRepository(User::class)->findOneBy([
                'resetToken' => $token
            ]);
            if ($user === null) {
                $this->addFlash('error', 'unknown token');
                return $this->redirectToRoute('admin');
            }

            $user->setResetToken(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();

            $this->addFlash('succes', 'Password updated');

            return $this->redirectToRoute('admin');
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token
        ]);
    }
}
