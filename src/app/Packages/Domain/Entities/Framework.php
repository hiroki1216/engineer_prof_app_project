<?php

namespace App\Packages\Domain\Entities;

use App\Packages\Domain\Entities\Values\ImageUrl;

/**
 * Framework Entity.
 */
class Framework
{
    /**
     * Constructor.
     *
     * @param string[]|null $versions
     */
    public function __construct(
        private string $name,
        private ?array $versions,
        private Language $language,
        private ?ImageUrl $image_url,
    ) {
    }
}
