<?php

namespace App\DataResolver;

use App\DTO\ReviewData;
use App\Repository\ProductRepository;
use App\Service\BreadCrumsService;
use App\Validation\ReviewDataValidator;
use App\VO\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use App\Exceptions\WebHttpException\WebValidationException;

class ReviewDataResolver implements ArgumentValueResolverInterface
{

    /**
     * @var ReviewDataValidator
     */
    private $validator;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var string
     */
    private $numberOfLinesPerPage;

    /**
     * @var BreadCrumsService
     */
    private $breadcrumbsService;

    /**
     * @param ReviewDataValidator $reviewDataValidator
     * @param ProductRepository $productRepository
     * @param BreadCrumsService $breadCrumsService
     * @param string $numberOfLinesPerPage
     */
    public function __construct(
        ReviewDataValidator $reviewDataValidator,
        ProductRepository $productRepository,
        BreadCrumsService $breadCrumsService,
        string $numberOfLinesPerPage
    ) {
        $this->numberOfLinesPerPage = $numberOfLinesPerPage;
        $this->breadcrumbsService = $breadCrumsService;
        $this->productRepository = $productRepository;
        $this->validator = $reviewDataValidator;

    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return ReviewData::class === $argument->getType();
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $productId = $request->request->get('productId');
        $comment = $request->request->get('comment');
        $email = $request->request->get('email');
        $nickname = $request->request->get('nickname');
        $rating = $request->request->get('rating');

        $errors = $this->validator->validate($request->request->all());

        if (!empty($errors)) {
            $message = "Ошибка валидации отзыва о товаре. Аргументы: \r\n";
            foreach ($errors as $key => $error) {
                $message .= "$key : $error \r\n";
            }

            throw new WebValidationException(
                $errors,
                '404.html.twig', [
                    'bestsellerProducts' => $this->productRepository->getBestSellerProducts($this->numberOfLinesPerPage, 0),
                    'breadcrumbs' => $this->breadcrumbsService->createBreadcrumbs(),
                ], $message
            );
        }

        yield new ReviewData(
            $rating,
            $comment,
            new Email($email),
            $nickname,
            $productId
        );

    }
}