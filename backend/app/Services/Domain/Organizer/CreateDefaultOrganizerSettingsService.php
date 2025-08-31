<?php

namespace Evently\Services\Domain\Organizer;

use Evently\DomainObjects\Enums\ColorTheme;
use Evently\DomainObjects\Enums\OrganizerHomepageVisibility;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Repository\Interfaces\OrganizerSettingsRepositoryInterface;

class CreateDefaultOrganizerSettingsService
{
    public function __construct(
        private readonly OrganizerSettingsRepositoryInterface $organizerSettingsRepository
    )
    {
    }

    public function createOrganizerSettings(OrganizerDomainObject $organizer): void
    {
        /** @var ColorTheme $defaultTheme */
        $defaultTheme = config('app.organizer_homepage_default_theme');

        $this->organizerSettingsRepository->create([
            'organizer_id' => $organizer->getId(),
            'homepage_visibility' => OrganizerHomepageVisibility::PUBLIC->name,

            // Use the "Modern" theme as default
            'homepage_theme_settings' => $defaultTheme->getThemeData(),
        ]);
    }
}
