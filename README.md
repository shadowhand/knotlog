# Knotlog

[![Become a Supporter](https://img.shields.io/badge/patreon-sponsor%20me-e6461a.svg)](https://www.patreon.com/shadowhand)
[![Latest Stable Version](https://img.shields.io/packagist/v/knotlog/knotlog.svg)](https://packagist.org/packages/knotlog/knotlog)
[![License](https://img.shields.io/packagist/l/knotlog/knotlog.svg)](https://github.com/shadowhand/knotlog/blob/master/LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/shadowhand/knotlog/tests.yml?branch=main)](https://github.com/shadowhand/knotlog)

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
use Knotlog\Writer\FileWriter;

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
new FileWriter()->write($log);
```

The `Log` class implements `JsonSerializable`, so it can be directly encoded to JSON for structured logging systems.

## Collecting Multiple Entries

Knotlog also has an `append()` method that allows adding multiple values under a single key, without overwriting:

```php
$log->append('features', 'new_checkout');
$log->append('features', 'recommendations');
```

This is useful in situations where related items are populated by different parts of the codebase.

### Service Logging

Additionally, Knotlog provides a `LogList` class that can be passed to dependencies and used to attach multiple
entries under a single key. This is useful for cases where a service should be able to log multiple related items,
without directly having access to the main `Log` instance, such as:

- Collecting external API calls
- Tracking database statements executed
- Recording multiple commands or queries in a CQRS system

```php
use Knotlog\LogList;

$statements = new LogList();

// Attach the list to the main log.
$log->set('db', $statements);

// Pass the list to a database connection.
$db = new Connection(log: $statements, /* ... */);
```

Then, inside the service class (`Connection` in this example), use the `LogList` to add entries:

```php
$this->log->push(
    // This class would be application-specific and is not provided by Knotlog
    new LogQuery(
        sql: 'SELECT * FROM users WHERE id = ?',
        params: [5513],
    )
);

// Push an entry for every query executed, or specific queries of interest.
$this->log->push(
    new LogQuery(
        sql: 'SELECT * FROM orders WHERE user_id = ?',
        params: [5513],
    ),
);
```

⚠️ _LogList entries MUST be objects. Complex objects SHOULD implement JsonSerializable._

The `LogList` maintains insertion order and will encode as a JSON array:

```json
{
  "db": [
    {"sql": "SELECT * FROM users WHERE id = ?", "params": [5513]},
    {"sql": "SELECT * FROM orders WHERE user_id = ?", "params": [5513]}
  ]
}
```

## Exception Logging

Knotlog provides an `ExceptionLog` class to capture exception (`Throwable`) context.

```php
use Knotlog\Misc\ExceptionLog;

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

Knotlog provides a middleware to flag every response with a 400+ status code as an error in the log.
The error is set to the HTTP reason phrase (e.g., "Not Found", "Internal Server Error").
This is particularly useful when log sampling is in effect, ensuring that error responses are always logged.

```php
use Knotlog\Http\LogResponseError;

// Sets error to the reason phrase for 400+ status codes
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
use Knotlog\Writer\FileWriter;

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

The `LoggerWriter` outputs log events to any [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible logger
using message interpolation.

```php
use Knotlog\Writer\LoggerWriter;

// Use with any PSR-3 logger
$writer = new LoggerWriter($psrLogger);

// Customize the message and error keys
$writer = new LoggerWriter($psrLogger, messageKey: 'msg', errorKey: 'err');

// Write the log event
$writer->write($log);
```

The writer automatically routes log events to the appropriate log level:

- `error()` - when the log contains an error or exception (uses `{error}` placeholder)
- `info()` - for all other log events (uses `{message}` placeholder)

The entire log context is passed to the logger, allowing it to format and process the data according
to its own implementation. The message key can be customized to match your logging schema.

### SampledWriter

The `SampledWriter` is a decorator that samples log events based on a configurable rate, while always logging errors.

```php
use Knotlog\Writer\SampledWriter;
use Knotlog\Writer\FileWriter;

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

### ToggledWriter

The `ToggledWriter` is a decorator that allows log writing to be enabled or disabled at runtime.

```php
use Knotlog\Writer\ToggledWriter;
use Knotlog\Writer\FileWriter;

// Enabled by default
$writer = new ToggledWriter(new FileWriter());

// Or start disabled
$writer = new ToggledWriter(new FileWriter(), writeLogs: false);

// Disable writing at any point
$writer->disable();

// Re-enable writing
$writer->enable();

// Only writes if enabled
$writer->write($log);
```

This is useful for conditionally disabling log output based on runtime configuration, such as
feature flags or environment-specific settings.

## License

MIT License, see `LICENSE` file for details.
