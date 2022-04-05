<?php

namespace App\DTO;

use App\VO\Sort;

class FilterData
{
    /**
     * @var int | null
     */
    private $page;

    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var string|null
     */
    private $vendor;

    /**
     * @var int|null
     */
    private $rating;

    /**
     * @var bool|null
     */
    private $enStock;

    /**
     * @var string | null
     */
    private $color;

    /**
     * @var string | null
     */
    private $size;

    /**
     * @var string|null
     */
    private $material;

    /**
     * @var string|null
     */
    private $length;

    /**
     * @var string|null
     */
    private $diameter;

    /**
     * @var string|null
     */
    private $collectionName;

    /**
     * @var int|null
     */
    private $priceFrom;

    /**
     * @var int|null
     */
    private $priceTo;

    /**
     * @var bool
     */
    private $batteries;

    /**
     * @var Sort
     */
    private $sort;

    /**
     * @param int $categoryId
     * @param int|null $page
     * @param string|null $vendor
     * @param int|null $rating
     * @param bool|null $enStock
     * @param string|null $material
     * @param string|null $length
     * @param string|null $diameter
     * @param string|null $collectionName
     * @param int|null $priceFrom
     * @param int|null $priceTo
     * @param string|null $color
     * @param string|null $size
     * @param bool $batteries
     * @param Sort $sort
     */
    public function __construct(
        Sort $sort,
        ?int $categoryId,
        int $page,
        ?string $vendor = null,
        ?int $rating = null,
        ?bool $enStock = null,
        ?string $material = null,
        ?string $length = null,
        ?string $diameter = null,
        ?string $collectionName = null,
        ?int $priceFrom = null,
        ?int $priceTo = null,
        ?string $color = null,
        ?string $size = null,
        ?bool $batteries = false
    ) {
        $this->page = $page;
        $this->categoryId = $categoryId;
        $this->vendor = $vendor;
        $this->rating = $rating;
        $this->enStock = $enStock;
        $this->material = $material;
        $this->length = $length;
        $this->diameter = $diameter;
        $this->collectionName = $collectionName;
        $this->priceFrom = $priceFrom;
        $this->priceTo = $priceTo;
        $this->color = $color;
        $this->size = $size;
        $this->batteries = $batteries;
        $this->sort = $sort;
    }

    /**
     * @return Sort
     */
    public function getSort(): Sort
    {
        return $this->sort;
    }

    /**
     * @return bool
     */
    public function isBatteries(): ?bool
    {
        return $this->batteries;
    }

    /**
     * @return string|null
     */
    public function getSize(): ?string
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    /**
     * @return int|null
     */
    public function getRating(): ?int
    {
        return $this->rating;
    }

    /**
     * @return string | null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @return string | null
     */
    public function getVendor(): ?string
    {
        return $this->vendor;
    }

    /**
     * @return string|null
     */
    public function getMaterial(): ?string
    {
        return $this->material;
    }

    /**
     * @return string|null
     */
    public function getDiameter(): ?string
    {
        return $this->diameter;
    }

    /**
     * @return string|null
     */
    public function getCollectionName(): ?string
    {
        return $this->collectionName;
    }

    /**
     * @return bool|null
     */
    public function getEnStock(): ?bool
    {
        return $this->enStock;
    }

    /**
     * @return string|null
     */
    public function getLength(): ?string
    {
        return $this->length;
    }

    /**
     * @return int|null
     */
    public function getPriceFrom(): ?int
    {
        return $this->priceFrom;
    }

    /**
     * @return int|null
     */
    public function getPriceTo(): ?int
    {
        return $this->priceTo;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }
}