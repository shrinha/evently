<?php

namespace Evently\Resources\Attendee;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\Enums\QuestionBelongsTo;
use Evently\Resources\CheckInList\AttendeeCheckInResource;
use Evently\Resources\Order\OrderResource;
use Evently\Resources\Question\QuestionAnswerViewResource;
use Evently\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AttendeeDomainObject
 */
class AttendeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getId(),
            'order_id' => $this->getOrderId(),
            'product_id' => $this->getProductId(),
            'product_price_id' => $this->getProductPriceId(),
            'event_id' => $this->getEventId(),
            'email' => $this->getEmail(),
            'status' => $this->getStatus(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'public_id' => $this->getPublicId(),
            'short_id' => $this->getShortId(),
            'locale' => $this->getLocale(),
            'notes' => $this->getNotes(),
            'product' => $this->when(
                !is_null($this->getProduct()),
                fn() => new ProductResource($this->getProduct()),
            ),
            'check_in' => $this->when(
                condition: $this->getCheckIn() !== null,
                value: fn() => new AttendeeCheckInResource($this->getCheckIn()),
            ),
            'order' => $this->when(
                condition: !is_null($this->getOrder()),
                value: fn() => new OrderResource($this->getOrder())
            ),
            'question_answers' => $this->when(
                condition: $this->getQuestionAndAnswerViews() !== null,
                value: fn() => QuestionAnswerViewResource::collection(
                    $this->getQuestionAndAnswerViews()
                        ?->filter(fn($qav) => $qav->getBelongsTo() === QuestionBelongsTo::PRODUCT->name)
                )
            ),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
        ];
    }

}
