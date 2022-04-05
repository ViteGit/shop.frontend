<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Seo;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SeoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use DateTimeImmutable;

class SitemapController extends AbstractController
{
    const ALWAYS = 'always';
    const HOURLY = 'hourly';
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MOUNTHLY = 'monthly';
    const YEARLY = 'yearly';
    const NEVER = 'never';

    /**
     * @var SeoRepository
     */
    private $seoRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @param SeoRepository $seoRepository
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        SeoRepository $seoRepository,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository
    ){
        $this->seoRepository = $seoRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @Route("/sitemap.xml", name="sitemap")
     *
     * @return string
     */
    public function actionIndex()
    {
        $pages = array_map(function (Seo $seo) {
            $updateAt = empty($seo->getUpdateAt()) ? new DateTimeImmutable() : $seo->getUpdateAt();
                return [
                    'loc' => $this->generateUrl($seo->getRoute(), [],  UrlGeneratorInterface::ABSOLUTE_URL),
                    'changefreq' => self::DAILY,
                    'lastmod' => $updateAt->format('Y-m-d'),
                    'priority' => 0.9,
                ];
        }, $this->seoRepository->findPages());

        $categories = array_map(function(Category $category) {
           $categoryId = $category->getId();
           $categorySlug = $category->getSlug();
           $seo = $category->getSeo();
           $updateAt = empty($seo->getUpdateAt()) ? new DateTimeImmutable() : $seo->getUpdateAt();

            return [
                'loc' => $this->generateUrl('product_list_by_category_id_and_slug', [
                        'slug' => $categorySlug,
                        'id' => $categoryId,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'changefreq' => self::MOUNTHLY,
                'lastmod' => $updateAt->format('Y-m-d'),
                'priority' => 0.5,
            ];
        }, $this->categoryRepository->findAll());

        $products = array_map(function(Product $product) {
           $productId = $product->getId();
           $productSlug = $product->getSlug();
           $seo = $product->getSeo();
           $updateAt = empty($seo->getUpdateAt()) ? new DateTimeImmutable() : $seo->getUpdateAt();

            return [
                'loc' => $this->generateUrl('product_by_id_and_slug', [
                        'slug' => $productSlug,
                        'id' => $productId,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'changefreq' => self::DAILY,
                'lastmod' => $updateAt->format('Y-m-d'),
                'priority' => 0.5,
            ];
        }, $this->productRepository->findAll());

        $response = new Response($this->renderView(
                'sitemap.xml.twig', ['urls' => array_merge($pages, $products, $categories)]
            )
        );

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
