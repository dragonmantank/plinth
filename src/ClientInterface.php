<?php

declare(strict_types=1);

namespace Dragonmantank\Plinth;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    public function create(array $body, string $uri = '', array $headers = []);
    public function delete(string $id, array $headers = []);
    public function get($id, array $query = [], array $headers = []);
    public function getLastRequest(): RequestInterface;
    public function getLastResponse(): ResponseInterface;
    public function send(RequestInterface $request): ResponseInterface;
    public function submit(array $formData = [], string $uri = '', array $headers = []);
    public function update(string $id, array $body, array $headers = []);
}
