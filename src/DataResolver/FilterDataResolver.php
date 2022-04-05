<?php

namespace App\DataResolver;

use App\DTO\FilterData;
use App\Validation\FilterDataValidator;
use App\VO\Sort;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Generator;
use App\Exceptions\WebHttpException\WebValidationException;

class FilterDataResolver implements ArgumentValueResolverInterface
{

    /**
     * @var FilterDataValidator
     */
    private $validator;

    /**
     * @param FilterDataValidator $validator
     */
    public function __construct(FilterDataValidator $validator)
    {
        $this->validator = $validator;

    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return FilterData::class === $argument->getType();
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
        $categoryId = $request->query->get('category_id');
        $page = $request->query->get('page') ?? 1;
        $vendor = $request->query->get('vendor');
        $rating = $request->query->get('rating');
        $enStock = $request->query->get('enStock');
        $color = $request->query->get('color');
        $size = $request->query->get('size');
        $material = $request->query->get('material');
        $length = $request->query->get('length');
        $diameter = $request->query->get('diameter');
        $collectionName = $request->query->get('collection_name');
        $priceFrom = $request->query->get('price_from');
        $priceTo = $request->query->get('price_to');
        $batteries = $request->query->get('batteries');
        $sort = $request->query->get('sort');

//        $errors = $this->validator->validate($request->request->all());
//        if (!empty($errors)) {
//            throw new WebValidationException(
//                $errors,
//                'feedback.html.twig'
//            );
//        }

        yield new FilterData(
            empty($sort) ? new Sort(Sort::NEW) : new Sort($sort),
            $categoryId,
            $page,
            $vendor,
            $rating,
            $enStock,
            $material,
            $length,
            $diameter,
            $collectionName,
            $priceFrom,
            $priceTo,
            empty($color) ? null : $color,
            $size,
            $batteries === null ? null : (bool) $batteries
        );
    }
}