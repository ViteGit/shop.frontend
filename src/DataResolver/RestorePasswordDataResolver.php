<?php

namespace App\DataResolver;

use App\DTO\RestorePasswordData;
use App\Repository\UserRepository;
use App\Validation\RestorePasswordValidator;
use App\VO\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use App\Exceptions\WebHttpException\WebValidationException;

class RestorePasswordDataResolver implements ArgumentValueResolverInterface
{
    /**
     * @var RestorePasswordValidator
     */
    private $validator;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param RestorePasswordValidator $restorePasswordValidator
     * @param UserRepository $userRepository
     */
    public function __construct(
        RestorePasswordValidator $restorePasswordValidator,
        UserRepository $userRepository
    ) {
        $this->validator = $restorePasswordValidator;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return RestorePasswordData::class === $argument->getType();
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $email = $request->get('email');

        $errors = $this->validator->validate($request->request->all());

        if (!empty($errors)) {
            throw new WebValidationExceptio($errors, 'restore_password.html.twig', [
                'email' => $email,
            ]);
        }

        $email = new Email($email);

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (empty($user)) {
            $errors = ['email' => "Пользователь с email $email не найден"];
        }

        if (!empty($errors)) {
            throw new WebValidationException($errors, 'restore_password.html.twig', [
                'email' => $email->getValue(),
            ]);
        }

        yield new RestorePasswordData($email);
    }
}
