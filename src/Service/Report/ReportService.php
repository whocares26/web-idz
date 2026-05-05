<?php

declare(strict_types=1);

namespace App\Service\Report;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves a report generator by format.
 *
 * Adding a new format is a matter of dropping in a new class implementing
 * ReportGeneratorInterface — services.yaml tags it automatically and this
 * service picks it up.
 */
final class ReportService
{
    /** @var array<string, ReportGeneratorInterface> */
    private array $generators = [];

    /**
     * @param iterable<ReportGeneratorInterface> $generators
     */
    public function __construct(iterable $generators)
    {
        foreach ($generators as $generator) {
            $this->generators[$generator->format()] = $generator;
        }
    }

    /**
     * @param iterable<\App\Entity\Order> $orders
     */
    public function generate(string $format, iterable $orders): Response
    {
        if (!isset($this->generators[$format])) {
            throw new NotFoundHttpException(sprintf('Unknown report format "%s".', $format));
        }

        return $this->generators[$format]->generate($orders);
    }

    /**
     * @return list<string>
     */
    public function supportedFormats(): array
    {
        return array_keys($this->generators);
    }
}
