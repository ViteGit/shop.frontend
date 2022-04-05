<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Component\Routing\RouterInterface;

class BreadCrumsService
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param Category|null $category
     * @param Product|null $product
     * @param bool $lastIsLink
     * @return array
     */
    public function createBreadcrumbs(?Category $category = null, ?Product $product = null, $lastIsLink = false): array
    {
        $breadcrumbs[] = [
            'name' => 'Главная',
            'path' => $this->router->generate('homepage'),
        ];

        if (!empty($category)) {
            $title = $category->getTitle();
            $id = $category->getId();
            $pCategory = $category->getParent();

            if ($pCategory == null) {
                $breadcrumbs[] = [
                    'name' => $title,
                    'path' => $this->router->generate('product_list_by_category_id_and_slug', [
                        'id' => $id,
                        'slug' => $category->getSlug()])
                ];
            } else {
                $breadcrumbs[] = [
                    'name' => $pCategory->getTitle(),
                    'path' => $this->router->generate(
                        'product_list_by_category_id_and_slug', [
                            'id' => $pCategory->getId(),
                            'slug' => $pCategory->getSlug()
                        ]
                    )
                ];

                $breadcrumbs[] = [
                    'name' => $category->getTitle(),
                    'path' => $this->router->generate('product_list_by_category_id_and_slug', [
                        'id' => $category->getId(),
                        'slug' => $category->getSlug()])
                ];
            }
        }

        if (!empty($product)) {
            foreach($product->getCategories() as $category) {
                $breadcrumbs[] = [
                    'name' => $category->getTitle(),
                    'path' => $this->router->generate('product_list_by_category_id_and_slug', [
                        'id' => $category->getId(),
                        'slug' => $category->getSlug()])
                ];
            }

            $breadcrumbs[] = array_merge($breadcrumbs, [
                    'name' => $product->getName(),
                    'path' => $this->router->generate('product_by_id_and_slug', ['id' => $product->getId(), 'slug' => $product->getSlug()])
                ]
            );
        }

        if (false === $lastIsLink) {
            $breadcrumbs[array_key_last($breadcrumbs)]['path'] = null;
        }

        return $breadcrumbs;
    }}