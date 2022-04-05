<?php

namespace App\Controller;

use App\DTO\FeedbackData;
use App\Entity\Feedback;
use App\Repository\ProductRepository;
use App\Repository\SeoRepository;
use App\Repository\SliderRepository;
use App\VO\Sort;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

class HomepageController extends AbstractController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SliderRepository
     */
    private $sliderRepository;

    /**
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param SliderRepository $sliderRepository
     */
    public function __construct(
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        SliderRepository $sliderRepository
    ) {
        $this->em = $entityManager;
        $this->productRepository = $productRepository;
        $this->sliderRepository = $sliderRepository;
    }

    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function homepage(): Response
    {
        return $this->render('homepage.html.twig', [
            'bestsellerProducts' => $this->productRepository->findByFilters(new Sort(Sort::BESTSELLER), true,8),
            'recommendedProducts' => $this->productRepository->findByFilters(new Sort(Sort::RECOMMENDED),true,8),
            'discountProducts' => $this->productRepository->findByFilters(new Sort(Sort::DISCOUNT), true,8),
            'newProducts' => $this->productRepository->findByFilters(new Sort(Sort::NEW), true, 8),
            'sliders' => $this->sliderRepository->findAll(),
        ]);
    }

    /**
     * @Route("/contacts", name="contacts", methods={"GET"})
     */
    public function contacts(): Response
    {
        return $this->render('info/contacts.html.twig');
    }

    /**
     * @Route("/privacy-policy", name="privacy_policy", methods={"GET"})
     */
    public function privacyPolicy(): Response
    {
        return $this->render('info/privacy_policy.html.twig');
    }

    /**
     * @Route("/offerta", name="offerta", methods={"GET"})
     */
    public function offerta(): Response
    {
        return $this->render('info/offerta.html.twig');
    }

    /**
     * @Route("/payment-info", name="payment_info", methods={"GET"})
     */
    public function paymentInfo(): Response
    {
        return $this->render('info/payment_info.html.twig');
    }

    /**
     * @Route("/delivery-info", name="delivery_info", methods={"GET"})
     */
    public function deliveryInfo(): Response
    {
        return $this->render('info/delivery_info.html.twig');
    }

    /**
     * @Route("/refund-info", name="refund_info", methods={"GET"})
     */
    public function refundInfo(): Response
    {
        return $this->render('info/refund_info.html.twig');

    }

    /**
     * @Route("/feedback", name="feedback", methods={"GET"})
     */
    public function showFeedback(): Response
    {
        return $this->render('feedback.html.twig');
    }

    /**
     * @Route("/feedback", name="add_feedback", methods={"POST"})
     *
     * @param FeedbackData $feedbackData
     *
     * @return Response
     *
     * @throws Exception
     */
    public function sendFeedback(FeedbackData $feedbackData): Response
    {
        $this->em->persist(
            new Feedback(
                $feedbackData->getName(),
                $feedbackData->getMessage(),
                $feedbackData->getEmail()
            )
        );

        $this->em->flush();

        $this->addFlash(
            'feedback-success-message',
            'Ваше сообщение успешно отправлено, в ближайшее время мы свяжемся с вами'
        );

        return $this->render('feedback.html.twig');
    }
}
