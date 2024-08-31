<?php

namespace App\Packages\Domain\Entities;

use App\Packages\Domain\Entities\Common\ImageUrl;

/**
 * ExternalServiceItem Entity.
 */
class ExternalServiceItem
{
    /**
     * Constructor.
     *
     * @param string[]|null $versions
     */
    public function __construct(
        private string $name,
        private ?array $versions,
        private ?ImageUrl $image_url,
    ) {
    }
}
