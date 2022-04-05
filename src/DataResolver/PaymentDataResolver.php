<?php

namespace App\DataResolver;

use App\DTO\Robokassa\PaymentData;
use App\Exceptions\Robokassa\RobokassaException;
use App\Service\Robokassa\RobokassaService;
use App\Validation\PaymentDataValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class PaymentDataResolver implements ArgumentValueResolverInterface
{
    /**
     * @var PaymentDataValidator
     */
    private $validator;

    /**
     * @var RobokassaService
     */
    private $robokassaService;

    /**
     * @param PaymentDataValidator $paymentDataValidator
     * @param RobokassaService $robokassaService
     */
    public function __construct(
        PaymentDataValidator $paymentDataValidator,
        RobokassaService $robokassaService
    ) {
        $this->robokassaService = $robokassaService;
        $this->validator = $paymentDataValidator;
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return PaymentData::class === $argument->getType();
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return \Generator|iterable
     *
     * @throws RobokassaException
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $outSum = $request->request->get('OutSum');
        $invId = $request->request->get('InvId');
        $shpItem = $request->request->get('Shp_item');
        $signature = $request->request->get('SignatureValue');

        $errors = $this->validator->validate($request->request->all());

        if (!empty($errors)) {
            throw new RobokassaException([], 'Ошибка валидации платежа', Response::HTTP_BAD_REQUEST);
        }

        yield new PaymentData(
            $outSum,
            $invId,
            $signature
        );
    }
}