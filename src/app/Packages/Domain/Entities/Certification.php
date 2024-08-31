<?php

namespace App\Packages\Domain\Entities;

use App\Packages\Domain\Entities\Values\ImageUrl;
use Carbon\Carbon;

/**
 * Certification Entity.
 */
class Certification
{
    /**
     * Constructor.
     */
    public function __construct(
        private string $name,
        private ?ImageUrl $image_url,
        private ?string $organization_name,
        private ?Carbon $acquisition_date,
        private ?int $score,
    ) {
    }
}
