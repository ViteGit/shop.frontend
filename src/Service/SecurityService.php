<?php

namespace App\Service;


use App\Entity\User;
use App\Exceptions\EntityException\EntityExistsException;
use App\Repository\UserRepository;
use App\VO\Email;
use App\VO\Gender;
use App\VO\Password;
use App\VO\PhoneNumber;
use App\VO\UserRole;
use App\VO\UserStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Exception;

class SecurityService
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em
    ) {
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    /**
     * @param string $login
     * @param string $fio
     * @param Email $email
     * @param PhoneNumber $phone
     * @param Gender $gender
     * @param Password $password
     *
     * @return void
     *
     * @throws ORMException
     * @throws Exception
     */
    public function registerUser(
        string $login,
        string $fio,
        Email $email,
        PhoneNumber $phone,
        Gender $gender,
        Password $password = null
    ): User {
        if ($this->userRepository->checkByPhone($phone)) {
            throw new EntityExistsException("Пользователь с номером телефона {$phone->getValue()} уже существует");
        }

        $user = new User($login, $fio, $email, $phone, $password, [UserRole::ROLE_USER], $gender);

        $this->userRepository->add($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @param User $user
     *
     * @return User
     */
    public function activateUser(User $user): User
    {
        $user->updateStatus(new UserStatus(UserStatus::ACTIVE));
        $this->em->flush();

        return $user;
    }

    /**
     * @param User $user
     *
     * @return User
     *
     * @throws Exception
     */
    public function updateLastLogin(User $user): User
    {
        $user->setLastLogin();
        $this->em->flush();

        return $user;
    }

    /**
     * @param User $user
     * @return User
     * @throws Exception
     */
    public function updatePasswordResetToken(User $user): User
    {
        $user->setPasswordResetToken();
        $this->em->flush();

        return $user;
    }

    /**
     * @param User $user
     *
     * @param Password $password
     */
    public function updatePassword(User $user, Password $password)
    {
        $user->setPassword($password);
        $user->dropPasswordResetToken();

        $this->em->flush();
    }
}
