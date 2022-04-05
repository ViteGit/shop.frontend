<?php

namespace App\DataResolver;

use App\DTO\CouponData;
use App\Repository\CouponRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\ShipmentMethodRepository;
use App\Validation\CouponDataValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use DateTimeImmutable;
use App\Exceptions\WebHttpException\WebValidationException;

class CouponDataResolver implements ArgumentValueResolverInterface
{
    /**
     * @var CouponDataValidator
     */
    private $validator;

    /**
     * @var CouponRepository
     */
    private $couponRepository;

    /**
     * @var ShipmentMethodRepository
     */
    private $shippingMethodRepository;

    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;

    /**
     * @param CouponDataValidator $cartDataValidator
     * @param CouponRepository $couponRepository
     * @param ShipmentMethodRepository $shipmentMethodRepository
     * @param PaymentMethodRepository $paymentMethodRepository
     */
    public function __construct(
        CouponDataValidator $cartDataValidator,
        CouponRepository $couponRepository,
        ShipmentMethodRepository $shipmentMethodRepository,
        PaymentMethodRepository $paymentMethodRepository
    ){
        $this->validator = $cartDataValidator;
        $this->couponRepository = $couponRepository;
        $this->shippingMethodRepository = $shipmentMethodRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return CouponData::class === $argument->getType();
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     * @throws \Exception
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $couponCode = $request->get('coupon_code');

        $errors = $this->validator->validate($request->query->all());

        if (!empty($errors)) {
            throw new WebValidationException($errors, 'checkout.html.twig', [
                'shipmentMethods' => $this->shippingMethodRepository->findAll(),
                'paymentMethods' => $this->paymentMethodRepository->findAll(),
            ]);
        }

        $coupon = $this->couponRepository->findOneBy(['code' => $couponCode]);

        if (empty($coupon)) {
            $errors = ['coupon_code' => 'Купон не найден'];
        }

        if (!empty($coupon)) {
            $expireAt = $coupon->getExpireAt();

            $diff = (new DateTimeImmutable())->diff($expireAt);

            if ($diff->invert > 0) {
                $errors = ['coupon_code' => "Купон истек ({$expireAt->format('Y-m-d')})"];
            }

            if (true == $coupon->isUsed()) {
                $errors = ['coupon_code' => "Купон уже использован"];
            }
        }

        if (!empty($errors)) {
            throw new WebValidationException($errors, 'checkout.html.twig', [
                'shipmentMethods' => $this->shippingMethodRepository->findAll(),
                'paymentMethods' => $this->paymentMethodRepository->findAll(),
                'couponCode' => $couponCode,
            ]);
        }

        yield new CouponData($coupon);
    }
}
