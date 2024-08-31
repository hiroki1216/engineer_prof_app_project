<?php

namespace App\Packages\Domain\Entities;

use App\Packages\Domain\Entities\Values\ImageUrl;

/**
 * ExternalService Entity.
 */
class ExternalService
{
    /**
     * Constructor.
     *
     * @param ExternalServiceItem[]|null $items
     * @param string[]|null              $versions
     */
    public function __construct(
        private string $name,
        private ?array $items,
        private ?array $versions,
        private ?ImageUrl $image_url,
    ) {
    }
}
