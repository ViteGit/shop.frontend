<?php

namespace App\Twig;

use App\Entity\Seo;
use App\Repository\CategoryRepository;
use App\Repository\SeoRepository;
use App\Service\CartService;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var SeoRepository
     */
    private $seoRepository;

    /**
     * @var RequestStack
     */
    private $request;

    /**
     * @param CartService $cartService
     * @param CategoryRepository $categoryRepository
     * @param SeoRepository $seoRepository
     * @param RequestStack $requestStack
     */
    public function __construct(
        CartService $cartService,
        CategoryRepository $categoryRepository,
        SeoRepository $seoRepository,
        RequestStack $requestStack
    ) {
        $this->seoRepository = $seoRepository;
        $this->cartService = $cartService;
        $this->categoryRepository = $categoryRepository;
        $this->request = $requestStack;
    }

    /**
     * @return array | TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('cart', [$this, 'getCart']),
            new TwigFunction('categories', [$this, 'getCategories']),
            new TwigFunction('seo', [$this, 'getSeo']),
        ];
    }

    /**
     * @return \App\Entity\Cart | null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCart()
    {
        return $this->cartService->getCart();
    }

    public function getCategories()
    {
        return $this->categoryRepository->findAll();
    }

    /**
     * @param string|null $title
     * @return Seo | null
     */
    public function getSeo(): ?Seo
    {
        $seo = $this->seoRepository->findOneBy([
            'route' => $this->request->getCurrentRequest()->attributes->get('_route')
        ]);

        if (!empty($seo)) {
            $title = $seo->getTitle();
        }

        return $seo;
    }
}