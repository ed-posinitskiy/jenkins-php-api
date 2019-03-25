<?php

namespace Utarwyn\Jenkins\Error;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class ConnectionErrorException extends \Exception
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * ConnectionErrorException constructor.
     *
     * @param string                 $action
     * @param ResponseInterface|null $response
     * @param Throwable|null         $previous
     */
    public function __construct(string $action, ?ResponseInterface $response = null, Throwable $previous = null)
    {
        $message = $response
            ? $this->createBadResponseMessage($response, $action)
            : $this->createUndefinedMessage($action, $previous);

        parent::__construct(
            sprintf('Jenkins connection error: %s', $message),
            $response ? $response->getStatusCode() : 0,
            $previous
        );

        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     * @param string            $action
     *
     * @return string
     */
    private function createBadResponseMessage(ResponseInterface $response, string $action): string
    {
        return sprintf(
            '%u error (%s) when trying to access action %s',
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $action
        );
    }

    /**
     * @param string         $action
     * @param Throwable|null $previous
     *
     * @return string
     */
    private function createUndefinedMessage(string $action, ?Throwable $previous = null): string
    {
        $message = sprintf('Failed to access action %s', $action);

        if ($previous) {
            $message = sprintf('%s (%s)', $message, $previous->getMessage());
        }

        return $message;
    }
}
