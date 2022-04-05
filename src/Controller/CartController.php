<?php

namespace App\Controller;

use App\DTO\CheckoutData;
use App\DTO\CouponData;
use App\Repository\CategoryRepository;
use App\Repository\CouponRepository;
use App\Repository\OrderRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\ShipmentMethodRepository;
use App\Service\CartService;
use App\Service\Robokassa\RobokassaService;
use App\Service\TelegramBotService;
use App\VO\PaymentCode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CartController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ShipmentMethodRepository
     */
    private $shippingMethodRepository;

    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var CouponRepository
     */
    private $couponRepository;

    /**
     * @var RobokassaService
     */
    private $robokassaService;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var TelegramBotService
     */
    private $botService;

    /**
     * @var string
     */
    private $sitename;

    /**
     * @param CategoryRepository $categoryRepository
     * @param ShipmentMethodRepository $shipmentMethodRepository
     * @param PaymentMethodRepository $paymentMethodRepository
     * @param CouponRepository $couponRepository
     * @param CartService $cartService
     * @param OrderRepository $orderRepository
     * @param RobokassaService $robokassaService
     * @param TelegramBotService $botService
     * @param string $sitename
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ShipmentMethodRepository $shipmentMethodRepository,
        PaymentMethodRepository $paymentMethodRepository,
        CouponRepository $couponRepository,
        CartService $cartService,
        OrderRepository $orderRepository,
        RobokassaService $robokassaService,
        TelegramBotService $botService,
        string $sitename
    ) {
        $this->sitename = $sitename;
        $this->categoryRepository = $categoryRepository;
        $this->shippingMethodRepository = $shipmentMethodRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->couponRepository = $couponRepository;
        $this->cartService = $cartService;
        $this->orderRepository = $orderRepository;
        $this->robokassaService = $robokassaService;
        $this->botService = $botService;
    }

    /**
     * @Route("/checkout", name="checkout", methods={"GET"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkout()
    {
        $cart = $this->cartService->getCart();

        if (empty($cart)) {
            throw new NotFoundHttpException('Корзина пуста');
        }

        return $this->render('checkout.html.twig', [
            'categories' => $this->categoryRepository->findAll(),
            'shipmentMethods' => $this->shippingMethodRepository->getAll(),
            'paymentMethods' => $this->paymentMethodRepository->getAll(),
        ]);
    }

    /**
     * @Route("/checkout", name="create_order", methods={"POST"})
     *
     * @param CheckoutData $checkoutData
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createOrder(CheckoutData $checkoutData)
    {
        $cart = $this->cartService->getCart();

        if (empty($cart)) {
            throw new NotFoundHttpException('Корзина пуста');
        }

        $order = $this->cartService->createOrder($checkoutData);

        $paymentMethod = $order->getPaymentMethod();

        if ($paymentMethod->getCode() == PaymentCode::ROBOKASSA) {
            return $this->redirectToRoute('show_payment_page', [
                'orderUniqueId' => $order->getUniqueId(),
            ]);
        }

        $this->botService->sendMessage("Сформирован новый заказ с id = {$order->getOrderId()} на сайте $this->sitename");

        return $this->redirectToRoute('show_order', [
            'orderUniqueId' => $order->getUniqueId(),
        ]);
    }

    /**
     * @Route("/robokassa", name="show_payment_page", methods={"GET"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createPayment(Request $request)
    {
        $orderUniqueId = $request->get('orderUniqueId');
        $order = $this->orderRepository->findByUniqueId($orderUniqueId);

        return $this->render('payment_create.html.twig', [
            'order' => $order,
            'paymentUrl' => $this->robokassaService->getPaymentUrl(
                $order->getOrderId(),
                $order->getOrderPrice(),
                "Оплата заказа № {$order->getOrderId()}"
            )
        ]);
    }

    /**
     * @Route("/show-order", name="show_order", methods={"GET"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function showOrder(Request $request)
    {
        $orderUniqueId = $request->get('orderUniqueId');

        $order = $this->orderRepository->findByUniqueId($orderUniqueId);

        return $this->render('show_order.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/use-coupon", name="use_coupon", methods={"GET"})
     *
     * @param CouponData $couponData
     * @return RedirectResponse
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function useCoupon(CouponData $couponData): RedirectResponse
    {
        $this->cartService->addCoupon($couponData->getCoupon());

        return $this->redirect($this->generateUrl('checkout'));
    }
}
