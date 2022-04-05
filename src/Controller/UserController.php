<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/profile", name="profile", methods={"GET"})
     *
     * @return Response
     */
    public function actionProfile()
    {
        return $this->render('profile/profile.html.twig', [

        ]);
    }

    /**
     * @Route("/my-orders", name="my_orders", methods={"GET"})
     *
     * @return Response
     */
    public function myOrders()
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();

        $orders = $user->getOrders();

        $orders = $orders->filter(function (Cart $cart) {
            return $cart instanceof Order;
        });

        return $this->render('profile/my_orders.html.twig', [
            'orders' => $orders
        ]);
    }

    /**
     * @Route("/my-comments", name="my_comments", methods={"GET"})
     *
     * @return Response
     */
    public function myComments()
    {
        return $this->render('profile/my_comments.html.twig', [

        ]);
    }

}
