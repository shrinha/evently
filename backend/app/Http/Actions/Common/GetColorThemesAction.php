<?php

namespace Evently\Http\Actions\Common;

use Evently\DomainObjects\Enums\ColorTheme;
use Evently\Http\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetColorThemesAction extends BaseAction
{
    public function __invoke(): JsonResponse
    {
        return $this->jsonResponse(
            data: ColorTheme::getAllThemes(),
            wrapInData: true,
        );
    }
}
