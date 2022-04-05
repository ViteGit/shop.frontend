<?php

namespace App\DataResolver;

use App\DTO\RegistrationData;
use App\Repository\UserRepository;
use App\Validation\CheckoutDataValidator;
use App\Validation\RegistrationDataValidator;
use App\VO\Email;
use App\VO\Gender;
use App\VO\Password;
use App\VO\PhoneNumber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use App\Exceptions\WebHttpException\WebValidationException;

class RegistrationDataResolver implements ArgumentValueResolverInterface
{
    /**
     * @var CheckoutDataValidator
     */
    private $validator;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param RegistrationDataValidator $registrationDataValidator
     * @param UserRepository $userRepository
     */
    public function __construct(
        RegistrationDataValidator $registrationDataValidator,
        UserRepository $userRepository
    ) {
        $this->validator = $registrationDataValidator;
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
        return RegistrationData::class === $argument->getType();
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $login = $request->get('login');
        $fio = $request->get('fio');
        $email = $request->get('email');
        $phone = $request->get('phone');
        $gender = $request->get('gender');
        $city = $request->get('city');
        $address = $request->get('address');
        $postCode = $request->get('postcode');
        $pass = $request->get('password');
        $repeatPass = $request->get('repeat_password');
        $dateOfBirthday = null;

        $errors = $this->validator->validate($request->request->all());

        if (!empty($errors)) {
            throw new WebValidationException(
                $errors,
                'registration.html.twig',
                [
                    'phone' => $phone,
                    'email' => $email,
                    'fio' => $fio,
                    'city' => $city,
                    'address' => $address,
                    'postcode' => $postCode,
                    'gender' => $gender,
                    'login' => $login,
                ]
            );
        }

        $password = new Password($pass);
        $email = new Email($email);
        $phone = new PhoneNumber($phone);

        if (!$password->compare($repeatPass)) {
            $errors = ['password' => 'Введеные пароли не совпадают'];
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!empty($user)) {
            $errors = ['email' => 'email уже существует'];
        }

        $user = $this->userRepository->findOneBy(['phone' => $phone]);

        if (!empty($user)) {
            $errors = ['phone' => 'Пользователь с таким телефоном уже существует'];
        }

        if (!empty($errors)) {
            throw new WebValidationException(
                $errors,
                'registration.html.twig',
                [
                    'phone' => $phone->getValue(),
                    'email' => $email->getValue(),
                    'fio' => $fio,
                    'city' => $city,
                    'address' => $address,
                    'postcode' => $postCode,
                    'gender' => $gender,
                    'login' => $login,
                ]
            );
        }

        yield new RegistrationData(
            $login,
            $fio,
            new Email($email),
            new PhoneNumber($phone),
            new Gender($gender),
            $dateOfBirthday ?? null,
            $city ?? null,
            $address ?? null,
            $postCode ?? null,
            $password
        );
    }
}
