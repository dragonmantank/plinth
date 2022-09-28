# Plinth

A base interface and HTTP client for building SDKs. Plinth takes care of the
basic handling of talking to the HTTP API for you, and provides convienance
methods that help you build better APIs and SDKs. Think more about the problems
you are solving for your clients than building simple GET/PUT/POST requests.

If you want to provide awesome SDKs to your users, you need to do more than
just wrap HTTP calls. Tools like Guzzle make it super easy for anyone to make
an HTTP call. Your SDK should solve actual problems for users and cut down on
that HTTP boilerplate that they need to write. Let Plinth handle all the ugly
stuff, you focus on provide an use interface for users to use.

Plinth is [PSR-7](https://www.php-fig.org/psr/psr-7/) compatible, and accepts
any [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client to handle the
underlying HTTP requests. This makes it compatible with many HTTP clients like
[Guzzle](https://docs.guzzlephp.org/en/stable/) and the
[Symfony HTTP Client](https://symfony.com/doc/current/http_client.html).

## Why?

I have spent years building SDKs, not only as part of my day job but also 
because many companies do not provide PHP SDKs for their services. Most HTTP
APIs tend to be fairly REST-ish and provide the same CRUD operations. wrapping
HTTP is easy, and boring.

While I can just throw Guzzle at the problem, many times this means wrapping
Guzzle  (or any other HTTP client) with authentication mechanisms, headers, and
simliar items over and over. Why not just make it easy to add the few extra bits
like headers and auth, and have a pre-packaged way to make the HTTP calls?

The second thing that is deeply close to me is that an SDK is much more than just
a wrapper around an HTTP client. Anyone can add something like Guzzle to a
project and make an HTTP call. An SDK should make it easy to solve specific
problems the users face. An SDK should provide methods for making it easier to
guide users through complicated workflows, and should have a public API based
around problems, not URLs.

This is where Plinth comes in. Plinth lets you get away from thinking about the
raw HTTP calls and thinking more about wrapping important items. For developers
that are also working on the APIs, it lets you think about things in terms of
actions, not just HTTP verbs. Plinth is first and foremost an API client so
tries to hide much of the general HTTP process and has you focus on the data
coming in and out of your API.

## Requirements

* PHP 8.1+
* Any PSR-18 HTTP Client or an [HTTPlug Adapter](https://docs.php-http.org/en/latest/clients.html)

## Installation

Plinth can be installed via Composer.

```bash
$ composer require dragonmantank/plinth
```

We also recommend installing the appropriate HTTPlug adapter for whatever
HTTP client your application already uses, if it is not already PSR-18
compatible. A few common ones are:

* [`symfony/http-client`](https://github.com/symfony/http-client)
* [`php-http/guzzle5-adapter`](https://github.com/php-http/guzzle5-adapter)
* [`php-http/guzzle6-adapter`](https://github.com/php-http/guzzle6-adapter)
* [`php-http/guzzle7-adapter`](https://github.com/php-http/guzzle7-adapter)

## Usage

Plinth can be used either as a dependency in your SDK, or as a base class for
your SDK.

### As a class dependency

This is the recommended way to use Plinth. The SDK that you create will take
accept a `Dragonmantank\Plinth\ClientInterface` object as a constructor
dependency, and you can call the inject object in your SDK's business logic
when you need to make an HTTP call.

```php
use Dragonmantank\Plinth\ClientInterface;

class OurSDK
{
    public function __construct(protected ClientInterface $plinth)
    { }

    public function createUser($userData): User
    {
        $response = $this->plinth->create('user', $userData);
        $user = new User($response);

        return $user;
    }

    public function removeUser(User $user): void
    {
        $this->plinth->delete('user/' . $user->id);
    }
}
```

### As a base class

If your API is new, or you have an API where some parts of it are not fully
supported (for example, alpha or beta API routes), you can have your SDK just
extend the Plinth client directly to expose easy-to-use methods, or even the
ability to just send a PSR-7 request, to your API.

```php
use Dragonmantank\Plinth\Client;

class OurSDK extends Client
{
    // All the basic HTTP calls are available directly to the user

    public function createUser($userData): User
    {
        $response = $this->create('user', $userData);
        $user = new User($response);

        return $user;
    }
}
```

### Configuration Options

Plinth supports a few configuration options to help you control how API calls
are handled.

* `decode_json`: bool - If your API returns JSON, you can have Plinth automatically convert it to an associative array. This is enabled by default.
* `authentication_handler`: invokable - You can pass an invokable object or anonymous function as an authentication handler which can manipulate the Request to add your auth parameters