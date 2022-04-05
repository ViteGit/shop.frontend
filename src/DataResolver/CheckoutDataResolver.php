<?php

namespace App\DataResolver;

use App\DTO\CheckoutData;
use App\Exceptions\WebHttpException\WebValidationException;
use App\Repository\PaymentMethodRepository;
use App\Repository\ProductVariantRepository;
use App\Repository\ShipmentMethodRepository;
use App\Validation\CheckoutDataValidator;
use App\VO\Email;
use App\VO\PhoneNumber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class CheckoutDataResolver implements ArgumentValueResolverInterface
{
    /**
     * @var CheckoutDataValidator
     */
    private $validator;

    /**
     * @var ProductVariantRepository
     */
    private $productVariantRepository;

    /**
     * @var ShipmentMethodRepository
     */
    private $shippingMethodRepository;

    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;

    /**
     * @param CheckoutDataValidator $checkoutDataValidator
     * @param ProductVariantRepository $productVariantRepository
     * @param ShipmentMethodRepository $shipmentMethodRepository
     * @param PaymentMethodRepository $paymentMethodRepository
     */
    public function __construct(
        CheckoutDataValidator $checkoutDataValidator,
        ProductVariantRepository $productVariantRepository,
        ShipmentMethodRepository $shipmentMethodRepository,
        PaymentMethodRepository $paymentMethodRepository

    ) {
        $this->validator = $checkoutDataValidator;
        $this->productVariantRepository = $productVariantRepository;
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
        return CheckoutData::class === $argument->getType();
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $fio = $request->get('fio');
        $email = $request->get('email');
        $phone = $request->get('phone');
        $city = $request->get('city');
        $address = $request->get('address');
        $postCode = $request->get('postcode');
        $shipmentMethodCode = $request->get('shipmentMethod');
        $paymentMethodCode = $request->get('paymentMethod');
        $notes = $request->get('notes');
        $pickUpId = $request->get('pickpoint_id');
        $privacyPolicy = $request->get('privacy_policy');

        $errors = $this->validator->validate($request->request->all());

        $shipmentMethod = $this->shippingMethodRepository->findOneBy(['code' => $shipmentMethodCode]);
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => $paymentMethodCode]);

        if (empty($shipmentMethod)) {
            $errors = ['shipmentMethod' => 'выберите способ доставки из имеющегося списка'];
        }

        if (empty($paymentMethod)) {
            $errors = ['paymentMethod' => 'выберите способ оплаты из имеющегося списка'];
        }

        if (!empty($errors)) {
            throw new WebValidationException(
                $errors,
                'checkout.html.twig',
                [
                    'shipmentMethods' => $this->shippingMethodRepository->findAll(),
                    'paymentMethods' => $this->paymentMethodRepository->findAll(),
                    'phone' => $phone,
                    'email' => $email,
                    'fio' => $fio,
                    'city' => $city,
                    'address' => $address,
                    'postcode' => $postCode,
                    'notes' => $notes,
                    'pickpoint_id' => $pickUpId,
                    'privacy_policy' => $privacyPolicy,
                    'shipmentMethodCode' => $shipmentMethodCode,
                    'paymentMethodCode' => $paymentMethodCode,
                ]
            );
        }

        yield new CheckoutData(
            $fio,
            new Email($email),
            new PhoneNumber($phone),
            $city,
            $address,
            $paymentMethod,
            $shipmentMethod,
            $postCode,
            $notes,
            $pickUpId
        );
    }
}
