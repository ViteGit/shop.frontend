<?php

namespace App\Repository;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\VO\Sort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param int $id
     *
     * @return Product
     */
    public function getById(int $id): Product
    {
        $product = $this->findOneBy(['id' => $id]);

        if (empty($product)) {
            throw new NotFoundHttpException("товар с id = $id не найден");
        }

        return $product;
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getBestSellerProducts(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin(ProductVariant::class, 'pv', 'with', 'p.id = pv.product')
            ->innerJoin(CartItem::class, 'ci', 'with', 'pv.id = ci.productVariant')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param QueryBuilder $builder
     * @param Sort $sort
     * @param array $categoryIds
     * @param string|null $vendor
     * @param string|null $color
     * @param string|null $size
     * @param string|null $material
     * @param string|null $length
     * @param string|null $diameter
     * @param string|null $collectionName
     * @param int|null $priceFrom
     * @param int|null $priceTo
     * @param bool|null $enStock
     * @param bool|null $batteries
     * @return QueryBuilder
     */
    private function getFilteredLinesQuery(
        QueryBuilder $builder,
        Sort $sort,
        array $categoryIds = [],
        ?string $vendor = null,
        ?string $color = null,
        ?string $size = null,
        ?string $material = null,
        ?string $length = null,
        ?string $diameter = null,
        ?string $collectionName = null,
        ?int $priceFrom = null,
        ?int $priceTo = null,
        ?bool $enStock = null,
        ?bool $batteries = null
    ): QueryBuilder {

        if (null !== $batteries) {
            if (true === $batteries) {
                $builder->andWhere('p.productCharacteristicsData.batteries is not NULL');
            }

            if (false === $batteries) {
                $builder->andWhere('p.productCharacteristicsData.batteries is NULL');
            }
        }

        if (!empty($priceFrom)) {
            $builder->andWhere('p.price >= :priceFrom')
                ->setParameter(':priceFrom', $priceFrom);
        }

        if (!empty($priceTo)) {
            $builder->andWhere('p.price <= :priceTo')
                ->setParameter(':priceTo', $priceTo);
        }

        if (!empty($color) || !empty($size)) {
            $builder->innerJoin(ProductVariant::class, 'pv', 'with', 'p.id = pv.product');

            if (!empty($color)) {
                $builder->andWhere('pv.color = :color')
                    ->setParameter(':color', $color);
            }

            if (!empty($size)) {
                $builder->andWhere('pv.size = :size')
                    ->setParameter(':size', $size);
            }
        }

        if (!empty($material)) {
            $builder->andWhere("p.productCharacteristicsData.material LIKE '%$material%'");
        }

        if (!empty($length)) {
            $lengths = explode('-', $length);

            $builder->andWhere('p.productCharacteristicsData.lenght >= :lengthFrom')
                ->andWhere('p.productCharacteristicsData.lenght <= :lengthTo')
                ->setParameter(':lengthFrom', $lengths[0])
                ->setParameter(':lengthTo', $lengths[1]);
        }

        if (!empty($diameter)) {
            $diameters = explode('-', $diameter);

            $builder->andWhere('p.productCharacteristicsData.diameter >= :diameterFrom')
                ->andWhere('p.productCharacteristicsData.diameter <= :diameterTo')
                ->setParameter(':diameterFrom', $diameters[0])
                ->setParameter(':diameterTo', $diameters[1]);
        }

        if (!empty($collectionName)) {
            $builder->andWhere('p.productCharacteristicsData.collectionName = :collection_name')
                ->setParameter(':collection_name', $collectionName);
        }

        if (null !== $enStock) {
            $builder->andWhere('p.enStock = :enStock')
                ->setParameter(':enStock', $enStock);
        }

        if (!empty($categoryIds)) {
            $builder->leftJoin('p.categories', 'c')
                ->andWhere('c.id in (:categoryIds)')
                ->setParameter(':categoryIds', $categoryIds);
        }

        if (!empty($vendor)) {
            $builder->andWhere('p.vendor = :vendor')
                ->setParameter(':vendor', $vendor);
        }

        switch ($sort->getValue()) {
            case Sort::NEW:
                $builder->orderBy("p.id", 'DESC');
                break;
            case Sort::BESTSELLER:
                $builder->innerJoin(ProductVariant::class, 'pv', 'with', 'p.id = pv.product')
                    ->innerJoin(CartItem::class, 'ci', 'with', 'pv.id = ci.productVariant');

                break;
            case Sort::DISCOUNT:
                $builder->orderBy("p.discount",'DESC');

                break;
            case Sort::RECOMMENDED:
                $builder->orderBy("p.rating", 'DESC');
                break;

            case Sort::PRICE_ASC:
                $builder->orderBy("p.price", 'ASC');
                break;

            case Sort::PRICE_DESC:
                $builder->orderBy("p.price", 'DESC');
                break;
        }

        return $builder;
    }

    /**
     * @param Sort $sort
     * @param array $categoryIds
     * @param string|null $vendor
     * @param string|null $color
     * @param string|null $material
     * @param string|null $length
     * @param string|null $diameter
     * @param string|null $collectionName
     * @param int|null $priceFrom
     * @param int|null $priceTo
     * @param bool|null $enStock
     * @param bool|null $size
     * @param bool|null $batteries
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getFilteredLinesCount(
        Sort $sort,
        array $categoryIds = [],
        ?bool $enStock = null,
        ?string $vendor = null,
        ?string $color = null,
        ?string $material = null,
        ?string $length = null,
        ?string $diameter = null,
        ?string $collectionName = null,
        ?int $priceFrom = null,
        ?int $priceTo = null,
        ?bool $size = null,
        ?bool $batteries = null
    ): int {
        $builder = $this->getFilteredLinesQuery(
            $this->getEntityManager()
                ->createQueryBuilder()
                ->select('COUNT(p)')
                ->from(Product::class, 'p'),
            $sort,
            $categoryIds,
            $vendor,
            $color,
            $size,
            $material,
            $length,
            $diameter,
            $collectionName,
            $priceFrom,
            $priceTo,
            $enStock,
            $batteries
        );

        $result = $builder->getQuery()->getSingleScalarResult();

        return $result ?? 0;
    }

    /**
     * @param Sort $sort
     * @param bool $enStock
     * @param int | null $limit
     * @param int | null $offset
     * @param array $categoryIds
     * @param string|null $vendor
     * @param string|null $color
     * @param string|null $material
     * @param string|null $size
     * @param string|null $length
     * @param string|null $diameter
     * @param string|null $collectionName
     * @param int|null $priceFrom
     * @param int|null $priceTo
     * @param bool $batteries
     * @return array | Product[]
     */
    public function findByFilters(
        Sort $sort,
        ?bool $enStock = null,
        ?int $limit = null,
        ?int $offset = null,
        array $categoryIds = [],
        ?string $vendor = null,
        ?string $color = null,
        ?string $material = null,
        ?string $size = null,
        ?string $length = null,
        ?string $diameter = null,
        ?string $collectionName = null,
        ?int $priceFrom = null,
        ?int $priceTo = null,
        ?bool $batteries = null
    ) {
        $builder = $this->getFilteredLinesQuery(
            $this->createQueryBuilder('p'),
            $sort,
            $categoryIds,
            $vendor,
            $color,
            $size,
            $material,
            $length,
            $diameter,
            $collectionName,
            $priceFrom,
            $priceTo,
            $enStock,
            $batteries
        );

        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }

        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery()->getResult();
    }
}
