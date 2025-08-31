<?php

declare(strict_types=1);

namespace Evently\Validators;

use Evently\DomainObjects\Enums\QuestionBelongsTo;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\Generated\ProductDomainObjectAbstract;
use Evently\DomainObjects\Generated\QuestionDomainObjectAbstract;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\QuestionDomainObject;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\EventSettingsRepositoryInterface;
use Evently\Repository\Interfaces\ProductRepositoryInterface;
use Evently\Repository\Interfaces\QuestionRepositoryInterface;
use Evently\Validators\Rules\OrderQuestionRule;
use Evently\Validators\Rules\ProductQuestionRule;
use Illuminate\Routing\Route;

class CompleteOrderValidator extends BaseValidator
{
    public function __construct(
        private readonly QuestionRepositoryInterface      $questionRepository,
        private readonly ProductRepositoryInterface       $productRepository,
        private readonly EventSettingsRepositoryInterface $eventSettingsRepository,
        private readonly Route                            $route
    )
    {
    }

    public function rules(): array
    {
        $questions = $this->questionRepository
            ->loadRelation(
                new Relationship(ProductDomainObject::class, [
                    new Relationship(ProductPriceDomainObject::class)
                ])
            )
            ->findWhere(
                [QuestionDomainObjectAbstract::EVENT_ID => $this->route->parameter('event_id')]
            );

        $orderQuestions = $questions->filter(
            fn(QuestionDomainObject $question) => $question->getBelongsTo() === QuestionBelongsTo::ORDER->name
        );

        $productQuestions = $questions->filter(
            fn(QuestionDomainObject $question) => $question->getBelongsTo() === QuestionBelongsTo::PRODUCT->name
        );

        $products = $this->productRepository
            ->loadRelation(ProductPriceDomainObject::class)
            ->findWhere(
                [ProductDomainObjectAbstract::EVENT_ID => $this->route->parameter('event_id')]
            );

        /** @var EventSettingDomainObject $eventSettings */
        $eventSettings = $this->eventSettingsRepository->findFirstWhere([
            'event_id' => $this->route->parameter('event_id'),
        ]);

        $addressRules = $eventSettings->getRequireBillingAddress() ? [
            'order.address' => 'array',
            'order.address.address_line_1' => 'required|string|max:255',
            'order.address.address_line_2' => 'nullable|string|max:255',
            'order.address.city' => 'required|string|max:85',
            'order.address.state_or_region' => 'nullable|string|max:85',
            'order.address.zip_or_postal_code' => 'nullable|string|max:85',
            'order.address.country' => 'required|string|max:2',
        ] : [];

        return [
            'order.first_name' => ['required', 'string', 'max:40'],
            'order.last_name' => ['required', 'string', 'max:40'],
            'order.questions' => new OrderQuestionRule($orderQuestions, $products),
            'order.email' => 'required|email',
            'products' => new ProductQuestionRule($productQuestions, $products),
            ...$addressRules
        ];
    }

    public function messages(): array
    {
        return [
            'order.first_name' => __('First name is required'),
            'order.last_name' => __('Last name is required'),
            'order.email' => __('A valid email is required'),
            'order.address.address_line_1.required' => __('Address line 1 is required'),
            'order.address.city.required' => __('City is required'),
            'order.address.zip_or_postal_code.required' => __('Zip or postal code is required'),
            'order.address.country.required' => __('Country is required'),
        ];
    }
}
