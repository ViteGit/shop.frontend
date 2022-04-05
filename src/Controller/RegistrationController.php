<?php

namespace App\Controller;

use App\DTO\EmailData;
use App\DTO\RegistrationData;
use App\DTO\RestorePasswordData;
use App\DTO\UpdatePasswordData;
use App\Repository\UserRepository;
use App\Service\MailSender;
use App\Service\SecurityService;
use App\VO\UserStatus;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegistrationController extends AbstractController
{
    /**
     * @var SecurityService
     */
    private $securityService;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var MailSender
     */
    private $mailSender;

    /**
     * @var string
     */
    private $shopName;

    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var Security
     */
    private $security;

    /**
     * @param AuthenticationUtils $authenticationUtils
     * @param Security $security
     * @param SecurityService $securityService
     * @param UserRepository $userRepository
     * @param TokenStorageInterface $tokenStorage
     * @param MailSender $mailSender
     * @param string $shopName
     */
    public function __construct(
        AuthenticationUtils $authenticationUtils,
        Security $security,
        SecurityService $securityService,
        UserRepository $userRepository,
        TokenStorageInterface $tokenStorage,
        MailSender $mailSender,
        string $shopName
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->security = $security;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
        $this->securityService = $securityService;
        $this->mailSender = $mailSender;
        $this->shopName = $shopName;
    }

    /**
     * @Route("/register", name="show_register", methods={"GET"})
     *
     * @return Response
     */
    public function showRegister(): Response
    {
        return $this->render('registration.html.twig');
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     *
     * @param RegistrationData $registrationData
     *
     * @return Response
     *
     * @throws \Swift_IoException
     * @throws \Doctrine\ORM\ORMException
     */
    public function register(RegistrationData $registrationData): Response
    {
        $user = $this->securityService->registerUser(
            $registrationData->getLogin(),
            $registrationData->getFio(),
            $registrationData->getEmail(),
            $registrationData->getPhone(),
            $registrationData->getGender(),
            $registrationData->getPassword()
        );

//        $user = $this->userRepository->getByPhone($phone);

//        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
//
//        $this->tokenStorage->setToken($token);
//
//        $request->getSession()->set('_security_main', serialize($token));
//
//        $event = new InteractiveLoginEvent($request, $token);
//
//        (new EventDispatcher())->dispatch($event, "security.interactive_login");
//
        $template = $this->renderView('emails/activation_email.html.twig', ['user' => $user]);

        $this->addFlash(
            'email-confirmation',
            'Для активации вашего аккаунта пройдите по ссылке, которую мы отослали на вашу почту'
        );

        $this->mailSender->sendEmail(
            'Инфорфация о регистрации',
            $template,
            [$registrationData->getEmail()->getValue()],
            [],
            'text/html'
        );

        return $this->redirect($this->generateUrl('login'));
    }

    /**
     * @Route("/activate", name="activate", methods={"GET"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function activateByEmail(Request $request)
    {
        $token = $request->get('token');

        if (!empty($token)) {
            $user = $this->userRepository->getByEmailToken($token);

            if ($user->getStatus() != UserStatus::BLOCKED) {
                $this->securityService->activateUser($user);
            }
        }

        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->render('login.html.twig', [
            'lastUsername' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/logout", name="logout", methods={"GET"})
     */
    public function logout(): Response {}

    /**
     * @Route("/login_check", name="login_check", methods={"POST"})
     *
     * @return Response
     */
    public function login_check(): Response {}

    /**
     * @Route("/restore-password", name="restore_password", methods={"GET"})
     *
     * @param RestorePasswordData $restorePasswordData
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function restorePassword()
    {
        return $this->render('restore_password.html.twig');
    }

    /**
     * @Route("/restore-password", name="send_restoring_email", methods={"POST"})
     *
     * @param RestorePasswordData $restorePasswordData
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     */
    public function sendRestoringPasswordEmail(RestorePasswordData $restorePasswordData)
    {
        $user = $this->securityService->updatePasswordResetToken(
            $this->userRepository->getByEmail($restorePasswordData->getEmail())
        );

        $this->mailSender->sendEmail(
            'Восстановление пароля',
            $this->renderView('emails/restore_password_email.html.twig', ['user' => $user]),
            [$user->getEmail()->getValue()],
            [],
            'text/html'
        );

        $this->addFlash(
            'send-restoring-password-email',
            'На вашу почту мы отправили письмо с дальнейщими инструкциями для восстановления пароля'
        );

        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/update-password", name="update_password_show", methods={"GET"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function showUpdatePassword(Request $request) {
        $token = $request->get('token');

        if (empty($token)) {
            throw new BadRequestHttpException('Пустой токен');
        }

       $this->userRepository->getByPasswordResetToken($token);

        return $this->render('update_password.html.twig', ['token' => $token]);
    }

    /**
     * @Route("/update-password", name="update_password", methods={"POST"})
     *
     * @param Request $request
     * @param UpdatePasswordData $updatePasswordData
     *
     * @return Response
     *
     * @throws EntityNotFoundException
     */
    public function updatePassword(Request $request, UpdatePasswordData $updatePasswordData) {
        $token = $request->get('token');

        if (empty($token)) {
            throw new BadRequestHttpException('Пустой токен');
        }

        $user = $this->userRepository->getByPasswordResetToken($token);

        $this->securityService->updatePassword($user, $updatePasswordData->getPassword());

        $this->addFlash(
            'email-password-updated',
            'Пароль успешно обнавлен'
        );

        return $this->redirectToRoute('login');
    }


    /**
     * @Route("/news-subscribe", name="news_subscribe", methods={"POST"})
     *
     * @param EmailData $emailData
     */
    public function newsSubscribe(EmailData $emailData)
    {

    }
}
