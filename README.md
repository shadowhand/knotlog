# Knotlog

**Wide logging for PHP**

Knotlog is inspired by the ideas of [loggingsucks.com](https://loggingsucks.com) and implements wide logging
as a first-class pattern in PHP. Avoid the pitfalls of traditional logging by using a single structured log
event that captures all relevant context of a request as a canonical log line, dramatically improving
observability and debugging capabilities.

Transform debugging from archaeological grep sessions into analytical queries with structured, queryable data.

## The Problem with Traditional Logging

Traditional logging is broken:

- **String search is ineffective**: Logs are treated as unstructured text, making it difficult to correlate events
- **Optimized for writing, not querying**: Developers emit what is convenient, not what is useful
- **Missing business context**: Standard logs lack the rich dimensionality needed for real investigations

## The Wide Logging Solution

Instead of asking "what is my code doing?", ask "what happened to this request?"

A proper wide event contains:

- Infrastructure context (service name, version, region, deployment)
- Request metadata (method, path, duration, status)
- User information (ID, subscription level, account age)
- Business data (cart contents, payment method, features enabled)
- Error details (type, code, message, stack trace)

## Installation

```bash
composer require knotlog/knotlog
```

## Requirements

- PHP 8.4 or newer

## Basic Usage

```php
use Knotlog\Log;

$log = new Log();

// Build up context throughout your request
$log->set('user_id', $userId);
$log->set('request_method', $_SERVER['REQUEST_METHOD']);
$log->set('request_path', $_SERVER['REQUEST_URI']);
$log->set('subscription_tier', $user->subscriptionTier);
$log->set('cart_value', $cart->total());
$log->set('response_status', 200);
$log->set('duration_ms', $duration);

// At the end of the request, emit the wide event
echo json_encode($log);
```

The `Log` class implements `JsonSerializable`, so it can be directly encoded to JSON for structured logging systems.

## Exception Logging

Knotlog provides an `ExceptionLog` class to capture exception (`Throwable`) context.

```php
use Knotlog\ExceptionLog;

try {
    // Some code that may throw
} catch (Throwable) {
    $log->add('exception', ExceptionLog::fromThrowable($throwable));
}
```

⚠️ _The exception log includes the stack trace, which may be very large and include sensitive information.
It is recommended to set `zend.exception_ignore_args = On` in your `php.ini` to minimize the stack trace._

For convenience, `Log` provides a `hasError()` method that will return true if either the `exception` or `error`
keys are set. This is particularly useful for determining if the log should be output when sampling is enabled.

## HTTP Middleware

Knotlog provides a [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware to log request and response context.

```php
use Knotlog\Http\LogRequestResponse;

// Logs request and response metadata
$stack->add(new LogRequestResponse($log));
```

Knotlog provides a middleware to log uncaught exception context and generate error responses.

```php
use Knotlog\Http\LogRequestError;
use Knotlog\Http\ServerErrorResponseFactory;

// The error factory creates a response from a throwable
// The default factory creates a minimal 500 response
$errorFactory = new ServerErrorResponseFactory($responseFactory, $streamFactory);

// Logs uncaught exceptions and outputs an error response
$stack->add(new LogRequestError($errorFactory, $log));
```

Knotlog provides a middleware to flag every response with a 400+ status as `error: true` in the log.
This is particularly useful when log sampling is in effect, ensuring that error responses are always logged.

```php
use Knotlog\Http\LogResponseError;

// Flags every response with 400+ status codes as errors
$stack->add(new LogResponseError($log));
```

When using these middleware, the order should be (from first to last):

- `LogResponseError` - execute the request, flag error responses
- `LogRequestResponse` - log the request, execute the request, log the response
- `LogRequestError` - catch uncaught exceptions, log them, generate error response

⚠️ _The order may depend on the application middleware stack. Some frameworks prefer a last-in-first-out order._

## Console Events

Knotlog provides a [Symfony Console](https://symfony.com/doc/current/components/console.html) event listener
that logs command execution and error context.

```php
use Knotlog\Console\LogCommandError;
use Knotlog\Console\LogCommandEvent;
use Symfony\Component\Console\ConsoleEvents;

// Logs command metadata on execution
$eventDispatcher->addListener(ConsoleEvents::COMMAND, new LogCommandEvent($log));

// Logs command error context on failure
$eventDispatcher->addListener(ConsoleEvents::ERROR, new LogCommandError($log));
```

If you prefer not to use the event system, you can manually log console context:

```php
use Knotlog\Console\LogCommand;

// Log command metadata during execution
$log->set('console', LogCommand::fromCommand($command, $input));
```

## Log Writers

Knotlog provides a `LogWriter` interface to enable flexible output destinations for wide log events.
The interface defines a simple contract with a single method `write(Log $log): void`.

### FileWriter

The `FileWriter` writes log events as JSON-encoded lines to a file or stream.

```php
use Knotlog\Output\FileWriter;

// Write to stderr (default)
$writer = new FileWriter();

// Write to a specific file
$writer = new FileWriter('/var/log/app.log');

// Use custom JSON encoding flags
$writer = new FileWriter('php://stdout', JSON_PRETTY_PRINT);

// Write the log event
$writer->write($log);
```

Each log line is prefixed with a status indicator:

- `ERROR` - when the log contains an error or exception
- `INFO` - for all other log events

### LoggerWriter

The `LoggerWriter` outputs log events to any [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible logger.

```php
use Knotlog\Output\LoggerWriter;

// Use with any PSR-3 logger
$writer = new LoggerWriter($psrLogger);

// Use custom JSON encoding flags
$writer = new LoggerWriter($psrLogger, JSON_PRETTY_PRINT);

// Write the log event
$writer->write($log);
```

The writer automatically routes log events to the appropriate log level:

- `error()` - when the log contains an error or exception
- `info()` - for all other log events

### SampledWriter

The `SampledWriter` is a decorator that samples log events based on a configurable rate, while always logging errors.

```php
use Knotlog\Output\SampledWriter;
use Knotlog\Output\FileWriter;

// Sample 1 in 5 requests (20%)
$writer = new SampledWriter(new FileWriter(), 5);

// Sample 1 in 100 requests (1%)
$writer = new SampledWriter(new FileWriter(), 100);

// Write the log event (might be sampled out unless it's an error)
$writer->write($log);
```

The sampled writer ensures that:

- All log events with errors or exceptions are always written (sampling is bypassed)
- Non-error events are sampled at the specified rate
- The sampling decision is made once at construction time and applies to all writes

This is particularly useful for high-traffic applications where logging every successful request would be
prohibitively expensive, while still capturing all errors for debugging.

## License

MIT License, see `LICENSE` file for details.
