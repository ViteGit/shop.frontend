<?php

namespace App\DataResolver;

use App\DTO\CartData;
use App\Exceptions\WebHttpException\WebBadRequestException;
use App\Repository\ProductRepository;
use App\Repository\ProductVariantRepository;
use App\Service\BreadCrumsService;
use App\Validation\CartDataValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use App\Exceptions\WebHttpException\WebValidationException;

class CartDataResolver implements ArgumentValueResolverInterface
{
    /**
     * @var CartDataValidator
     */
    private $validator;

    /**
     * @var ProductVariantRepository
     */
    private $productVariantRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var BreadCrumsService
     */
    private $breadcrumbsService;

    /**
     * @var string
     */
    private $numberOfLinesPerPage;

    /**
     * @param CartDataValidator $cartDataValidator
     * @param ProductVariantRepository $productVariantRepository
     * @param ProductRepository $productRepository
     * @param BreadCrumsService $breadcrumbsService
     * @param string $numberOfLinesPerPage
     */
    public function __construct(
        CartDataValidator $cartDataValidator,
        ProductVariantRepository $productVariantRepository,
        ProductRepository $productRepository,
        BreadCrumsService $breadcrumbsService,
        string $numberOfLinesPerPage
    )
    {
        $this->breadcrumbsService = $breadcrumbsService;
        $this->validator = $cartDataValidator;
        $this->productVariantRepository = $productVariantRepository;
        $this->productRepository = $productRepository;
        $this->numberOfLinesPerPage = $numberOfLinesPerPage;
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return CartData::class === $argument->getType();
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $variantId = $request->get('variantId');
        $quantity = $request->get('quantity');

        $errors = $this->validator->validate($request->request->all());

        if (!empty($errors)) {
            $variant = $this->productVariantRepository->getById($variantId);
            $product = $variant->getProduct();

            throw new WebValidationException($errors, 'product.html.twig', [
                'product' => $product,
                'bestsellerProducts' => $this->productRepository->getBestSellerProducts($this->numberOfLinesPerPage, 0),
                'breadcrumbs' => $this->breadcrumbsService->createBreadcrumbs(null, $product),
                'seo' => $product->getSeo(),
            ]);
        }

        yield new CartData(
            $variantId,
            $quantity
        );
    }
}
