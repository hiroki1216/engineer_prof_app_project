<?php

namespace App\Packages\Domain\Entities;

use App\Packages\Domain\Entities\Values\ImageUrl;

/**
 * Language Entity.
 */
class Language
{
    /**
     * Constructor.
     *
     * @param string[]|null    $versions
     * @param Framework[]|null $frameworks
     */
    public function __construct(
        private string $name,
        private ?array $versions,
        private ?ImageUrl $image_url,
        private ?array $frameworks,
    ) {
    }
}
