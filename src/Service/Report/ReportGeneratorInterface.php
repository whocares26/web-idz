<?php

declare(strict_types=1);

namespace App\Service\Report;

use Symfony\Component\HttpFoundation\Response;

interface ReportGeneratorInterface
{
    /**
     * Short identifier used in URLs (csv, xlsx, pdf).
     */
    public function format(): string;

    /**
     * @param iterable<\App\Entity\Order> $orders
     */
    public function generate(iterable $orders): Response;
}
