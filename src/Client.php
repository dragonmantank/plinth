<?php

declare(strict_types=1);

namespace Dragonmantank\Plinth;

use Http\Discovery\Psr18ClientDiscovery;
use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientInterface as PSRClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements ClientInterface
{
    protected PSRClientInterface $client;
    protected RequestInterface $lastRequest;
    protected ResponseInterface $lastResponse;

    public function __construct(protected string $baseUrl, protected array $options = [], PSRClientInterface $client = null)
    {
        // Helper methods all assume that the baseURL ends in a /
        if ('/' !== substr($this->baseUrl, -1)) {
            $this->baseUrl .= '/';
        }

        $defaultOptions = [
            'authentication_handler' => null,
            'decode_json' => true,
        ];

        $this->options = array_merge($defaultOptions, $this->options);

        $this->client = $client ?: Psr18ClientDiscovery::find();
    }

    public function create(array|string $body, string $uri = '', array $headers = [])
    {
        $request = new Request(
            $this->baseUrl . $uri,
            'POST',
            'php://temp',
            $headers,
        );

        if (is_array($body)) {
            $body = json_encode($body);
        }

        $request->getBody()->write($body);

        return $this->process($request);
    }

    public function delete(string $id, array $headers = [])
    {
        $request = new Request(
            $this->baseUrl . $id,
            'DELETE',
            'php://temp',
            $headers,
        );

        return $this->process($request);
    }

    public function get($id, array $query = [], array $headers = [])
    {
        $request = new Request(
            $this->baseUrl . $id . '?' . http_build_query($query),
            'GET',
            'php://temp',
            $headers,
        );

        return $this->process($request);
    }

    public function getLastRequest(): RequestInterface
    {
        return $this->lastRequest;
    }

    public function getLastResponse(): ResponseInterface
    {
        return $this->lastResponse;
    }

    protected function process(RequestInterface $request)
    {
        $request = $this->processAuthentication($request);

        $response = $this->send($request);
        $data = $response->getBody()->getContents();
        $response->getBody()->rewind();

        if ($this->options['decode_json']) {
            $data = json_decode($data, true);
        }

        return $data;
    }

    protected function processAuthentication(RequestInterface $request)
    {
        if ($this->options['authentication_handler']) {
            $request = $this->options['authentication_handler']($request);
        }

        return $request;
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        $this->lastRequest = $request;
        $this->lastResponse = $this->client->sendRequest($request);

        return $this->lastResponse;
    }

    public function submit(array $formData = [], string $uri = '', array $headers = [])
    {
        if (empty($headers)) {
            $headers = ['content-type' => 'application/x-www-form-urlencoded'];
        }

        $request = new Request(
            $this->baseUrl . $uri,
            'POST',
            'php://temp',
            $headers
        );

        $request->getBody()->write(http_build_query($formData));
        return $this->process($request);
    }

    public function update(string $id, array|string $body, array $headers = [])
    {
        $request = new Request(
            $this->baseUrl . $id,
            'PUT',
            'php://temp',
            $headers,
        );

        if (is_array($body)) {
            $body = json_encode($body);
        }

        $request->getBody()->write($body);
        return $this->process($request);
    }
}
