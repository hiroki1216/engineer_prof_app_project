<?php

namespace Tests\Mock\testdata;

use App\Packages\Domain\Entities\Certification;
use App\Packages\Domain\Entities\Project;
use App\Packages\Domain\Entities\Values\ImageUrl;
use App\Packages\Notification\Interface\TestEntityNotifierInterface;
use Carbon\Carbon;

class TestEntity
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

    /**
     * 通知オブジェクトを受け取り、エンティティのプロパティを通知します。
     * 目的としては、エンティティクラスのプロパティ値をprivateにしたまま、他のクラスにプロパティ値を渡すためです。
     *
     * @param TestEntityNotifierInterface $note
     * @return void
     */
    public function notify(TestEntityNotifierInterface $note): void
    {
        $note->setFirstName($this->first_name);
        $note->setLastName($this->last_name);
        $note->setBirthDate($this->birth_date);
        $note->setStartCarrierDate($this->start_carrier_date);
        $note->setEmail($this->email);
        $note->setPassword($this->password);
        $note->setImageUrl($this->image_url);
        $note->setCertifications($this->certifications);
        $note->setProjects($this->projects);
    }
}
