<?php

namespace App\Packages\Domain\Entities;

use Carbon\Carbon;

/**
 * Project Entity.
 */
class Project
{
    /**
     * Constructor.
     */
    public function __construct(
        private Engineer $owner_engineer,
        private string $name,
        private Carbon $start_on,
        private ?Carbon $end_on,
        private string $role,
        private string $number_of_members,
        private string $overview_of_project,
        private string $overview_of_job_description,
    ) {
    }
}
