<?php

namespace App\Support;

use App\Entity\SupportRequest;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class SupportRequestService
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    private int $dayMaxRequests;

    const DAY_MAX_REQUESTS_100 = 100;


    public function __construct(EntityManagerInterface $entityManager, int $dayMaxRequests = null)
    {
        $this->entityManager = $entityManager;
        if (!isset($dayMaxRequests)) {
            $dayMaxRequests = self::DAY_MAX_REQUESTS_100;
        }

        $this->dayMaxRequests = $dayMaxRequests;
    }

    public function getDayMaxRequests(): int
    {
        return $this->dayMaxRequests;
    }

    public function getTodayRequestsCount(): int
    {
        $repository = $this->entityManager->getRepository(SupportRequest::class);
        $today = new \DateTime('now');
        return $repository->createQueryBuilder('q')
            ->select('count(q.id)')
            ->andWhere('q.createdAt >= :today')
            ->setParameter('today', $today->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}