<?php

namespace Sylius\Component\Shipping\Model;

class ShipmentUnit
{

    /** @var mixed */
    protected $id;

    /** @var ShipmentInterface */
    protected $shipment;

    /** @var ShippableInterface */
    protected $shippable;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getShipment(): ?ShipmentInterface
    {
        return $this->shipment;
    }

    /**
     * {@inheritdoc}
     */
    public function setShipment(?ShipmentInterface $shipment): void
    {
        $this->shipment = $shipment;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippable(): ?ShippableInterface
    {
        return $this->shippable;
    }

    public function setShippable(?ShippableInterface $shippable): void
    {
        $this->shippable = $shippable;
    }
}
