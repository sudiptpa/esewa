<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Fakes;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class ArrayLogger implements LoggerInterface
{
    /** @var array<int, array{level:string, message:string, context:array<string,mixed>}> */
    public array $records = [];

    /**
     * @param string|\Stringable $message
     * @param array<mixed> $context
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, (string) $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<mixed> $context
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, (string) $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<mixed> $context
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, (string) $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<mixed> $context
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, (string) $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<mixed> $context
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, (string) $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<mixed> $context
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, (string) $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<mixed> $context
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, (string) $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<mixed> $context
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, (string) $message, $context);
    }

    /**
     * @param string $level
     * @param string|\Stringable $message
     * @param array<mixed> $context
     */
    public function log($level, $message, array $context = []): void
    {
        $normalizedContext = [];

        foreach ($context as $key => $value) {
            if (is_string($key)) {
                $normalizedContext[$key] = $value;
            }
        }

        $this->records[] = [
            'level' => (string) $level,
            'message' => (string) $message,
            'context' => $normalizedContext,
        ];
    }
}
