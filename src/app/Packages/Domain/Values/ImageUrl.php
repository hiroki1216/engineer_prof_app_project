<?php

namespace App\Packages\Domain\Entities\Values;

/**
 * ImageUrl Entity.
 */
class ImageUrl
{
    /**
     * Constructor.
     */
    public function __construct(
        private string $url,
    ) {
    }
}
