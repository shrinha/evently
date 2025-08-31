<?php

declare(strict_types=1);

namespace Evently\Http\Request\Affiliate;

use Evently\Http\Request\BaseRequest;
use Evently\Validators\Rules\AffiliateRules;

class UpdateAffiliateRequest extends BaseRequest
{
    public function rules(): array
    {
        return AffiliateRules::updateRules();
    }
}