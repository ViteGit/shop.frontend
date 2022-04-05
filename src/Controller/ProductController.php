<?php

namespace App\Controller;

use App\DTO\FilterData;
use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;
use App\VO\Sort;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var CartRepository
     */
    private $orderRepository;

    /**
     * @var CartService
     */
    private $orderService;

    /**
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param CartRepository $orderRepository
     * @param CartService $orderService
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        CartRepository $orderRepository,
        CartService $orderService
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }

    /**
     * @Route("/category/{id}/{slug}", name="product_list_by_category_id_and_slug")
     *
     * @param FilterData $filterData
     * @param int $id
     * @param string $slug
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function productsByCategoryIdAndSlug(FilterData $filterData, int $id, string $slug)
    {
        $category = $this->categoryRepository->getById($id);
        $seo = $category->getSeo();

        if ($seo->getSlug() != $slug) {
            return new RedirectResponse(
                $this->generateUrl('product_list_by_category_id_and_slug', [
                        'id' => $category->getId(),
                        'slug' => $category->getSlug()
                    ]
                ), Response::HTTP_MOVED_PERMANENTLY
            );
        }

        $limit = $this->getParameter('numberOfLinesPerPage');
        $page = (Request::createFromGlobals())->get('page') ?? 1;
        $offset = ($limit * $page) - $limit;

        $products = $this->productRepository->findByFilters(
            $filterData->getSort(),
            $filterData->getEnStock(),
            $limit,
            $offset,
            [$category->getId()],
            $filterData->getVendor(),
            $filterData->getColor(),
            $filterData->getMaterial(),
            $filterData->getSize(),
            $filterData->getLength(),
            $filterData->getDiameter(),
            $filterData->getCollectionName(),
            $filterData->getPriceFrom(),
            $filterData->getPriceTo(),
            $filterData->isBatteries()
        );

        $productCount = $this->productRepository->getFilteredLinesCount(
            $filterData->getSort(),
            [$category->getId()],
            $filterData->getEnStock(),
            $filterData->getVendor(),
            $filterData->getColor(),
            $filterData->getMaterial(),
            $filterData->getLength(),
            $filterData->getDiameter(),
            $filterData->getCollectionName(),
            $filterData->getPriceFrom(),
            $filterData->getPriceTo()
        );

        return $this->render('product_list.html.twig', [
            'productCount' => $productCount,
            'pageNumber' => $page,
            'pageCount' => ceil($productCount / $limit),
            'products' => $products,
            'category' => $category,
            'breadcrumbs' => $this->createBreadcrumbs($category),
            'seo' => $seo,
            'filterData' => $filterData,
        ]);
    }

    /**
     * @Route("/products/popular", name="popular_product_list")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function popularProducts(Request $request)
    {
        $limit = $this->getParameter('numberOfLinesPerPage');
        $page = $request->query->get('page') ?? 1;
        $offset = ($limit * $page) - $limit;
        $sort = new Sort(Sort::BESTSELLER);

        return $this->render('product_list.html.twig', [
            'breadcrumbs' => $this->createBreadcrumbs(),
            'pageNumber' => $page,
            'pageCount' => ceil($this->productRepository->getFilteredLinesCount($sort) / $limit),
            'products' => $this->productRepository->getBestSellerProducts($limit, $offset)
        ]);
    }

    /**
     * @Route("/products/recommended", name="recommended_product_list")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function recommendedProducts(Request $request)
    {
        $limit = $this->getParameter('numberOfLinesPerPage');
        $page = $request->query->get('page') ?? 1;
        $offset = ($limit * $page) - $limit;
        $sort = new Sort(Sort::RECOMMENDED);

        return $this->render('product_list.html.twig', [
            'breadcrumbs' => $this->createBreadcrumbs(),
            'pageNumber' => $page,
            'pageCount' => ceil($this->productRepository->getFilteredLinesCount($sort) / $limit),
            'products' => $this->productRepository->findByFilters($sort, null, $limit, $offset),
        ]);
    }

    /**
     * @Route("/products/new", name="new_product_list")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function newProducts(Request $request)
    {
        $limit = $this->getParameter('numberOfLinesPerPage');
        $page = $request->query->get('page') ?? 1;
        $offset = ($limit * $page) - $limit;
        $sort = new Sort(Sort::NEW);

        return $this->render('product_list.html.twig', [
            'breadcrumbs' => $this->createBreadcrumbs(),
            'pageNumber' => $page,
            'pageCount' => ceil($this->productRepository->getFilteredLinesCount($sort) / $limit),
            'products' => $this->productRepository->findByFilters($sort,null, $limit, $offset)
        ]);
    }


    /**
     * @Route("/products/discount", name="discount_product_list")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function discountProducts(Request $request)
    {
        $limit = $this->getParameter('numberOfLinesPerPage');
        $page = $request->query->get('page') ?? 1;
        $offset = ($limit * $page) - $limit;
        $sort = new Sort(Sort::DISCOUNT);

        return $this->render('product_list.html.twig', [
            'breadcrumbs' => $this->createBreadcrumbs(),
            'pageNumber' => $page,
            'pageCount' => ceil($this->productRepository->getFilteredLinesCount($sort) / $limit),
            'products' => $this->productRepository->findByFilters($sort, null, $limit, $offset)
        ]);
    }

    /**
     * @Route("/product/{id}/{slug}", name="product_by_id_and_slug")
     *
     * @param int $id
     * @param string $slug
     *
     * @return RedirectResponse|Response
     */
    public function productPage(int $id, string $slug)
    {
        $product = $this->productRepository->getById($id);
        $seo = $product->getSeo();

        if ($seo->getSlug() != $slug) {
            return new RedirectResponse(
                $this->generateUrl('product_by_id_and_slug', [
                        'id' => $product->getId(),
                        'slug' => $product->getSlug()]
                ), Response::HTTP_MOVED_PERMANENTLY
            );
        }

        return $this->render('product.html.twig', [
            'product' => $product,
            'bestsellerProducts' => $this->productRepository->getBestSellerProducts($this->getParameter('numberOfLinesPerPage'), 0),
            'breadcrumbs' => $this->createBreadcrumbs(null, $product),
            'seo' => $seo,
        ]);
    }

    /**
     * @param Category|null $category
     * @param Product|null $product
     * @param bool $lastIsLink
     * @return array
     */
    private function createBreadcrumbs(?Category $category = null, ?Product $product = null, $lastIsLink = false): array
    {
        $breadcrumbs[] = [
            'name' => 'Главная',
            'path' => $this->generateUrl('homepage'),
        ];

        if (!empty($category)) {
            $title = $category->getTitle();
            $id = $category->getId();
            $pCategory = $category->getParent();

            if ($pCategory == null) {
                $breadcrumbs[] = [
                    'name' => $title,
                    'path' => $this->generateUrl('product_list_by_category_id_and_slug', [
                        'id' => $id,
                        'slug' => $category->getSlug()])
                ];
            } else {
                $breadcrumbs[] = [
                    'name' => $pCategory->getTitle(),
                    'path' => $this->generateUrl(
                        'product_list_by_category_id_and_slug', [
                            'id' => $pCategory->getId(),
                            'slug' => $pCategory->getSlug()
                        ]
                    )
                ];

                $breadcrumbs[] = [
                    'name' => $category->getTitle(),
                    'path' => $this->generateUrl('product_list_by_category_id_and_slug', [
                        'id' => $category->getId(),
                        'slug' => $category->getSlug()])
                ];
            }
        }

        if (!empty($product)) {
            foreach ($product->getCategories() as $category) {
                $breadcrumbs[] = [
                    'name' => $category->getTitle(),
                    'path' => $this->generateUrl('product_list_by_category_id_and_slug', [
                        'id' => $category->getId(),
                        'slug' => $category->getSlug()])
                ];
            }

            $breadcrumbs[] = array_merge($breadcrumbs, [
                    'name' => $product->getName(),
                    'path' => $this->generateUrl('product_by_id_and_slug', ['id' => $product->getId(), 'slug' => $product->getSlug()])
                ]
            );
        }

        if (false === $lastIsLink) {
            $breadcrumbs[array_key_last($breadcrumbs)]['path'] = null;
        }

        return $breadcrumbs;
    }
}
