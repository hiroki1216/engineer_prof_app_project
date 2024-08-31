<?php

namespace App\Packages\Domain\Entities;

use App\Packages\Domain\Entities\Values\ImageUrl;
use Carbon\Carbon;

/**
 * Engineer Entity.
 */
class Engineer
{
    /**
     * Constructor.
     *
     * @param Certification[]|null $certifications
     * @param Project[]|null       $projects
     */
    public function __construct(
        private string $first_name,
        private string $last_name,
        private Carbon $birth_date,
        private Carbon $start_carrier_date,
        private string $email,
        private string $password,
        private ?ImageUrl $image_url,
        private ?array $certifications,
        private ?array $projects,
    ) {
    }
}
