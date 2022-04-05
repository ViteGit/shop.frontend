<?php

namespace App\Controller;

use App\Exceptions\Robokassa\RobokassaException;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use App\Service\Robokassa\RobokassaService;
use App\DTO\Robokassa\PaymentData;
use App\VO\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Exception;
use Doctrine\ORM\EntityNotFoundException;

class RobokassaController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var RobokassaService
     */
    private $robokassaService;

    /**
     * @param RobokassaService $robokassaService
     * @param EntityManagerInterface $entityManager
     * @param PaymentRepository $paymentRepository
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        RobokassaService $robokassaService,
        EntityManagerInterface $entityManager,
        PaymentRepository $paymentRepository,
        OrderRepository $orderRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->robokassaService = $robokassaService;
        $this->em = $entityManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @Route("/robokassa/callback", name="robokassa_callback", methods={"POST"})
     *
     * @param PaymentData $paymentData
     *
     * @return Response
     *
     * @throws EntityNotFoundException
     * @throws RobokassaException
     */
    public function callbackAction(PaymentData $paymentData)
    {
        $outSum = $paymentData->getOutSum();
        $invId = $paymentData->getInvId();
        $signature = $paymentData->getSignature();

        if (false === $this->robokassaService->validateResult($signature, $outSum, $invId)) {
            throw new RobokassaException([], 'Неверная подпись', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $payment = $this->paymentRepository->getByInvId($invId);

        if (false === $payment->isPending()) {
            throw new RobokassaException(
                [],
                "Неожиданный статус платежа, текущий статус = {$payment->getStatus()->getValue()}",
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        try {
            $this->robokassaService->approveAndDeposit($payment);
        } catch (Exception $e) {
            throw new RobokassaException([],'Что-то пошло не так', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response("OK$invId");
    }

    /**
     * @Route("/payment/success", name="payment_success", methods={"POST"})
     *
     * @param PaymentData $paymentData
     *
     * @return RedirectResponse | Response
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws RobokassaException
     * @throws Exception
     */
    public function successAction(PaymentData $paymentData): Response
    {
        $outSum = $paymentData->getOutSum();
        $invId = $paymentData->getInvId();
        $signature = $paymentData->getSignature();

        if (false === $this->robokassaService->validateSuccess($signature, $outSum, $invId)) {
            throw new RobokassaException([], 'Неверная подпись платежа', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $order = $this->orderRepository->findOneBy(['orderId' => $invId]);

        $payment = $order->getPayment()
            ->updateStatus(new PaymentStatus(PaymentStatus::COMPLETED))
            ->updatePaymentDate();

        $this->em->flush();

        return $this->render('success_payment.html.twig', [
            'order' => $payment->getOrder()
        ]);
    }

    /**
     * @Route("/payment/failed", name="payment_failed", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function failAction(Request $request): Response
    {
        $invId = $request->request->get('InvId');

        $order = $this->orderRepository->findOneBy(['orderId' => $invId]);

        $payment = $order->getPayment();
        $payment->updateStatus(new PaymentStatus(PaymentStatus::FAILED));

        $this->em->flush();

        return $this->render('failed_payment.html.twig', [
            'order' => $payment->getOrder()
        ]);
    }
}