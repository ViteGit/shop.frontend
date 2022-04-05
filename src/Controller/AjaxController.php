<?php

namespace App\Controller;

use App\DTO\CartData;
use App\DTO\ReviewData;
use App\Entity\Review;
use App\Entity\Reviewer;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\PickPointZoneRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductVariantRepository;
use App\Repository\ShipmentMethodRepository;
use App\Repository\UserRepository;
use App\Service\BreadCrumsService;
use App\Service\CartService;
use App\Service\GeoIpService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Exception;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @Route("ajax/v1")
 */
class AjaxController extends AbstractController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var CartRepository
     */
    private $orderRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var ProductVariantRepository
     */
    private $productVariantReposirory;

    /**
     * @var CartItemRepository
     */
    private $cartItemRepository;

    /**
     * @var ShipmentMethodRepository
     */
    private $shipmentMethodRepository;

    /**
     * @var PickPointZoneRepository
     */
    private $pickPointZoneRepository;

    /**
     * @var GeoIpService
     */
    private $geoIpService;

    /**
     * @var BreadCrumsService
     */
    private $breadcrumbsService;

    /**
     * @param ProductRepository $productRepository
     * @param CartRepository $orderRepository
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     * @param CartService $cartServuce
     * @param ProductVariantRepository $productVariantReposirory
     * @param CartItemRepository $cartItemRepository
     * @param ShipmentMethodRepository $shipmentMethodRepository
     * @param PickPointZoneRepository $pickPointZoneRepository
     * @param GeoIpService $geoIpService
     * @param BreadCrumsService $breadcrumbsService
     */
    public function __construct(
        ProductRepository $productRepository,
        CartRepository $orderRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        Security $security,
        CartService $cartServuce,
        ProductVariantRepository $productVariantReposirory,
        CartItemRepository $cartItemRepository,
        ShipmentMethodRepository $shipmentMethodRepository,
        PickPointZoneRepository $pickPointZoneRepository,
        GeoIpService $geoIpService,
        BreadCrumsService $breadcrumbsService

    ) {
        $this->em = $entityManager;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->cartService = $cartServuce;
        $this->productVariantReposirory = $productVariantReposirory;
        $this->cartItemRepository = $cartItemRepository;
        $this->shipmentMethodRepository = $shipmentMethodRepository;
        $this->pickPointZoneRepository = $pickPointZoneRepository;
        $this->geoIpService = $geoIpService;
        $this->breadcrumbsService = $breadcrumbsService;
    }

    /**
     * @param ReviewData $reviewData
     *
     * @Route("/add-review", name="add_review", methods={"POST"})
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function addReview(ReviewData $reviewData)
    {
        $product = $this->productRepository->getById($reviewData->getProductId());

        $review = new Review(
            $reviewData->getRating(),
            $reviewData->getComment()
        );

        $reviewer = new Reviewer($reviewData->getEmail(), $reviewData->getNickname());
        $reviewer->addReview($review);
        $product->addReview($review);

        $review->updateReviewer($reviewer);
        $review->updateProduct($product);

        $rating = 0;
        foreach ($product->getReviews() as $review)
        {
            $rating += $review->getRating();
        }

        $product->updateRating(round($rating / $product->getReviews()->count(), 0, PHP_ROUND_HALF_UP));

        $this->em->flush();

        return new RedirectResponse($this->generateUrl(
            'product_by_id_and_slug',
            [
                'id' => $product->getId(),
                'slug' => $product->getSlug()
            ])
        );
    }

    /**
     * @param CartData $cartData
     *
     * @return RedirectResponse
     *
     * @throws NonUniqueResultException
     * @throws Exception
     *
     * @Route("/add-to-cart", name="add_to_cart_by_form", methods={"POST"})
     *
     */
    public function addToCart(CartData $cartData): Response
    {
        $cart = $this->cartService->getCart();

        $user = $this->security->getUser();

        if (empty($cart)) {
            $this->cartService->initCart($user);
        }

        $productVariant = $this->productVariantReposirory->getById($cartData->getVariantId());

        $this->cartService->addItem(
            $productVariant,
            $cartData->getQuantity()
        );

        $product = $productVariant->getProduct();

        return $this->redirectToRoute('product_by_id_and_slug', [
            'id' => $product->getId(),
            'slug' => $product->getSlug(),
        ]);
    }

    /**
     * @Route("/remove-from-cart/{itemId}", name="remove_from_cart")
     *
     * @param int $itemId
     *
     * @return RedirectResponse
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function removeFromCart(int $itemId): RedirectResponse
    {
        $cart = $this->cartService->getCart();

        $user = $this->security->getUser();

        if (empty($cart)) {
            $this->cartService->initCart($user);
        }

        $cartItem = $this->cartItemRepository->findOneBy(['id' => $itemId]);

        $this->cartService->removeItem($cartItem);

        return $this->redirectToRoute('checkout', []);
    }

    /**
     * @Route("/render-address-form", name="render_address_form")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws EntityNotFoundException
     */
    public function renderAddressForm(Request $request)
    {
        $code = $request->request->get('code');

        $shipmentMethod = $this->shipmentMethodRepository->findOneBy(['code' => $code]);

        if (empty($shipmentMethod)) {
            throw new EntityNotFoundException();
        }

        if ($code === 'pick_point') {
            $template = 'checkout/pickpoint-address-form.html.twig';
        } elseif ($code === 'post_of_russia') {
            $template = 'checkout/post-of-russia-address-form.html.twig';
        } elseif ($code === 'moscow_pickup') {
            $template = 'checkout/moscow-pickup-address-form.html.twig';
        } else {
            $template = 'checkout/address-form.html.twig';
        }

        return new Response($this->renderView($template));
    }
}
