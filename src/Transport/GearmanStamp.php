<?php

namespace SD\Gearman\Transport;

use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @author Brian Feaver <brian.feaver@slickdeals.net>
 */
final class GearmanStamp implements StampInterface
{
    private $function;

    public function __construct(string $function)
    {
        if ('' === $function) {
            throw new InvalidArgumentException('Function name cannot be blank.');
        }
        $this->function = $function;
    }

    public function getFunction(): string
    {
        return $this->function;
    }
}
