<?php

namespace App\Service;

use App\DTO\CheckoutData;
use App\DTO\ShippingData;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Coupon;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\ProductVariant;
use App\Entity\Shipment;
use App\Entity\User;
use App\Repository\CartRepository;
use App\VO\PaymentStatus;
use App\VO\ShipmentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\NonUniqueResultException;

class CartService
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $sessionKeyName;


    /**
     * @var CartRepository
     */
    private $cartRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $bonusInterestRate;

    /**
     * @var string
     */
    private $freeDeliveryFrom;

    /**
     * @param SessionInterface $session
     * @param string $sessionKeyName
     * @param CartRepository $cartRepository
     * @param EntityManagerInterface $entityManager
     * @param int $bonusInterestRate
     * @param string $freeDeliveryFrom
     */
    public function __construct(
        SessionInterface $session,
        string $sessionKeyName,
        CartRepository $cartRepository,
        EntityManagerInterface $entityManager,
        int $bonusInterestRate,
        string $freeDeliveryFrom
    ) {
        $this->session = $session;
        $this->sessionKeyName = $sessionKeyName;
        $this->cartRepository = $cartRepository;
        $this->em = $entityManager;
        $this->bonusInterestRate = $bonusInterestRate;
        $this->freeDeliveryFrom = $freeDeliveryFrom;
    }

    /**
     * @return Cart
     *
     * @throws NonUniqueResultException
     */
    public function getCart(): ?Cart
    {
        if (!$this->session->has($this->sessionKeyName)) {
            return null;
        }

        $cart = $this->cartRepository->findByUniqueId($this->session->get($this->sessionKeyName));

        if ($cart instanceof Order) {
            return null;
        }

        return $cart;
    }

    /**
     * @param User $user
     *
     * @return Cart
     *
     * @throws Exception
     */
    public function initCart(?User $user)
    {
        $sessionValue = uniqid();

        $this->session->set($this->sessionKeyName, $sessionValue);

        $order = new Cart(
            $sessionValue,
            [],
            $user
        );

        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }

    /**
     * @param ProductVariant $productVariant
     * @param int $quantity
     *
     * @return Cart
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function addItem(ProductVariant $productVariant, int $quantity): Cart
    {
        $cart = $this->getCart();

        /**
         * @var CartItem $item
         */

        $isVariantExists = false;
        foreach ($cart->getItems() as $item) {
            if ($isVariantExists = $item->getProductVariant() === $productVariant) {
                $item->updateQuantity($quantity);
                break;
            };
        }

        if (false === $isVariantExists) {
            $item = new CartItem($cart, $productVariant, $quantity);
            $this->em->persist($item);
            $cart->addItem($item);
        }

        $this->em->flush();

        return $cart;
    }

    /**
     * @param CartItem $cartItem
     *
     * @return Cart
     *
     * @throws NonUniqueResultException
     */
    public function removeItem(CartItem $cartItem): Cart
    {
        $cart = $this->getCart();

        $cart->removeItem($cartItem);
        $cartItem->dropCart();

        $this->em->persist($cart);
        $this->em->flush();

        return $cart;
    }

    /**
     * @param Coupon $coupon
     *
     * @return Cart
     *
     * @throws NonUniqueResultException
     */
    public function addCoupon(Coupon $coupon): Cart
    {
        $cart = $this->getCart();

        $coupon->addCart($cart);
        $coupon->setUsed();
        $this->em->flush();

        return $cart;
    }

    /**
     * @param Cart $cart
     * @param User $user
     */
    public function updateUser(Cart $cart, User $user)
    {
        $cart->updateUser($user);

        $this->em->flush();
    }

    /**
     * @param CheckoutData $checkoutData
     *
     * @return Order
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function createOrder(CheckoutData $checkoutData): Order
    {
        $cart = $this->getCart();

        $user = $cart->getUser();

        $orderPrice = $cart->getFinalPrice();

        $points = 0;

        if (!empty($user)) {
            $user->updatePoints(0);

            $points = ($cart->getTotalPrice() / 100) * $this->bonusInterestRate;
        }

//        логика формирования цены доставки в будущем вместо фиксированой будет динамической
        $shipmentPrice = $this->freeDeliveryFrom < $orderPrice ? 0 : $checkoutData->getShipmentMethod()->getPrice();

        $order = new Order(
            $orderPrice + $shipmentPrice,
            $shipmentPrice,
            $cart->getId(),
            $points,
            $cart->getUniqueId(),
            $cart->getItems()->toArray(),
            $checkoutData->getNotes(),
            $cart->getUser(),
            $cart->getCoupons()->toArray(),
            new Payment(
                $checkoutData->getPaymentMethod(),
                null,
                new PaymentStatus(PaymentStatus::PENDING),
                'RU',
                $orderPrice
            ),
            new Shipment(
                new ShipmentStatus(ShipmentStatus::PENDING),
                $checkoutData->getShipmentMethod(),
                new ShippingData(
                    $checkoutData->getFio(),
                    $checkoutData->getPhone(),
                    $checkoutData->getEmail(),
                    $checkoutData->getCity(),
                    $checkoutData->getAddress(),
                    $checkoutData->getPickUpId(),
                    $checkoutData->getPostCode()
                ),
                null
            )
        );

        $order = $this->updateCartReferences($cart, $order);

        $payment = $order->getPayment();
        $payment->updateInvId($payment->getId());
        $this->em->flush();

        return $order;
    }

    /**
     * @param Cart $cart
     * @param Order $order
     *
     * @return Order
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function updateCartReferences(Cart $cart, Order $order)
    {
        $this->em->getConnection()->beginTransaction();

        try {
            foreach ($cart->getItems() as $item) {
                $item->updateCart($order);
            }

            foreach ($cart->getCoupons() as $coupon) {
                $coupon->updateCart($order);
            }

            $this->em->remove($cart);
            $this->em->flush();

            $this->em->persist($order);
            $this->em->flush();
            $this->em->getConnection()->commit();

            return $order;
        } catch (Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }
}
