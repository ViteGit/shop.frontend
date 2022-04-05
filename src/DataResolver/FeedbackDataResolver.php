<?php

namespace App\DataResolver;

use App\DTO\FeedbackData;
use App\Validation\FeedbackDataValidator;
use App\Validation\ReviewDataValidator;
use App\VO\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Generator;
use App\Exceptions\WebHttpException\WebValidationException;

class FeedbackDataResolver implements ArgumentValueResolverInterface
{

    /**
     * @var ReviewDataValidator
     */
    private $validator;

    /**
     * @param FeedbackDataValidator $feedbackDataValidator
     */
    public function __construct(FeedbackDataValidator $feedbackDataValidator)
    {
        $this->validator = $feedbackDataValidator;

    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return FeedbackData::class === $argument->getType();
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return Generator | iterable
     *
     * @throws WebValidationException
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {

        $message = $request->get('message');
        $email = $request->get('email');
        $name = $request->get('name');

        $errors = $this->validator->validate($request->request->all());

        if (!empty($errors)) {
            throw new WebValidationException(
                $errors,
                'feedback.html.twig'
            );
        }

        yield new FeedbackData(
            $message,
            $name,
            new Email($email)
        );
    }
}