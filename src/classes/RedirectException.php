<?php

namespace Cronbeat;

/**
 * Exception thrown when a controller needs to redirect to another page.
 * Contains headers that should be sent before the redirect.
 */
class RedirectException extends \Exception {
    /**
     * @var array<string, string> Headers to be sent
     */
    private array $headers;

    /**
     * @param array<string, string> $headers Headers to be sent
     * @param string $message Exception message
     * @param int $code Exception code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        array $headers,
        string $message = 'Redirect',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->headers = $headers;
    }

    /**
     * Get the headers to be sent
     *
     * @return array<string, string> Headers to be sent
     */
    public function getHeaders(): array {
        return $this->headers;
    }
}