<?php

namespace Evently\Services\Application\Handlers\Event\DTO;

use Evently\DataTransferObjects\AddressDTO;
use Evently\DataTransferObjects\Attributes\CollectionOf;
use Evently\DataTransferObjects\AttributesDTO;
use Evently\DataTransferObjects\BaseDTO;
use Evently\DomainObjects\Enums\EventCategory;
use Evently\DomainObjects\Status\EventStatus;
use Illuminate\Support\Collection;

class UpdateEventDTO extends BaseDTO
{
    public function __construct(
        public readonly string         $title,
        public readonly ?EventCategory $category,
        public readonly int            $account_id,
        public readonly int            $id,
        public readonly ?string        $start_date = null,
        public readonly ?string        $end_date = null,
        public readonly ?string        $description = null,
        #[CollectionOf(AttributesDTO::class)]
        public readonly ?Collection    $attributes = null,
        public readonly ?string        $timezone = null,
        public readonly ?string        $currency = null,
        public readonly ?string        $location = null,
        public readonly ?AddressDTO    $location_details = null,
        public readonly ?string        $status = EventStatus::DRAFT->name,
    )
    {
    }
}
