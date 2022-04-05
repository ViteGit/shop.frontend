<?php

namespace App\DataResolver;

use App\DTO\RestorePasswordData;
use App\DTO\UpdatePasswordData;
use App\Repository\UserRepository;
use App\Validation\RestorePasswordValidator;
use App\Validation\UpdatePasswordDataValidator;
use App\VO\Email;
use App\VO\Password;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use App\Exceptions\WebHttpException\WebValidationException;

class UpdatePasswordDataResolver implements ArgumentValueResolverInterface
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
     * @param UpdatePasswordDataValidator $updatePasswordValidator
     * @param UserRepository $userRepository
     */
    public function __construct(
        UpdatePasswordDataValidator $updatePasswordValidator,
        UserRepository $userRepository
    ) {
        $this->validator = $updatePasswordValidator;
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
        return UpdatePasswordData::class === $argument->getType();
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $pass = $request->get('password');
        $repeatPass = $request->get('confirm_password');
        $token = $request->get('token');

        $errors = $this->validator->validate([
            'password' => $pass,
            'confirm_password' => $repeatPass,
        ]);

        $password = new Password($pass);

        if (!$password->compare($repeatPass)) {
            $errors = ['password' => 'Введеные пароли не совпадают'];
        }


        if (!empty($errors)) {
            throw new WebValidationException($errors, 'update_password.html.twig', [
                'token' => $token,
            ]);
        }

        yield new UpdatePasswordData($password);
    }
}
