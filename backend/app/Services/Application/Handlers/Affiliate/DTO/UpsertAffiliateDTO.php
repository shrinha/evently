<?php

declare(strict_types=1);

namespace Evently\Services\Application\Handlers\Affiliate\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\DomainObjects\Status\AffiliateStatus;

class UpsertAffiliateDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $code,
        public ?string $email = null,
        public AffiliateStatus $status = AffiliateStatus::ACTIVE,
        public ?int $event_id = null,
        public ?int $account_id = null,
    ) {
    }
}