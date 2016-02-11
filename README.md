# php-events

|master|develop|
|------|-------|
|[![Build Status](https://travis-ci.org/kanellov/php-events.svg?branch=master)](https://travis-ci.org/kanellov/php-events)|[![Build Status](https://travis-ci.org/kanellov/php-events.svg?branch=develop)](https://travis-ci.org/kanellov/php-events)|

An single instance event manager, implemented with a simple php function. It allows you to attach and detach listeners to named events, trigger events and interrupt listeners from executing.

## Requirements

- php >= 5.3

## Installation

``` terminal
$ composer require kanellov/php-events
```

## Basic features

- **Set priority to listeners for each event.**
  When attaching a listener, you can set the priority by specifying an integer as the last argument. Listeners with greater priority value will be triggered first.
- **Collect listeners results when triggering an event.**
  The return values from each listener are collected when triggering and returned in an array, ordered according to listeners.
- **Stop event from propagating.**
  Events can be stopped at any time from listeners. Each listener receives a callable as the last argument, which prevents triggering the following listeners. The `stopped` flag is contained in the triggering results.
- **Wildcard event.**
  You can use the wildcard event `*` to attach to all events. If priority is specified it will be used for this listener for any event triggered.

## Usage

### Attaching listeners

- `\Knlv\events('on', string $event_name, callable $listener[, int $priority]);` registers a `$listener` for event `$event_name`
- `\Knlv\events('on', '*', callable $listener[, int priority])` registers a `$listener` for all events

**Note:** _All listeners receive an extra last argument. It' s a callable that stops event propagation if called._

### Triggering event

- `\Knlv\events('trigger', string $event_name [, mixed $...]);` triggers the `$event_name` event and passes args to listeners.

### Detaching listeners

- `\Knlv\events('off');` detaches all listeners
- `\Knlv\events('off', $event_name)` detaches all listeners for event `$event_name`
- `\Knlv\events('off', $event_name, $listener)` detaches a certain `$listener` for event `$event_name`

## Examples

### Attach listeners and trigger event
``` php
\Knlv\events('on', 'event_name', function ($value) {
    return strtolower($value);
});

\Knlv\events('on', 'event_name', function ($value) {
    return strtoupper($value);
});

$result = \Knlv\events('trigger', 'event_name', 'TEST');

var_dump($result);

/*
array(2) {
  'stopped' =>
  bool(false)
  'results' =>
  array(2) {
    [0] =>
    string(4) "test"
    [1] =>
    string(4) "TEST"
  }
}
*/
```

### Specifying priority

``` php
\Knlv\events('on', 'event_name', function ($value) {
    return strtolower($value);
});

\Knlv\events('on', 'event_name', function ($value) {
    return strtoupper($value);
}, 10);

$result = \Knlv\events('trigger', 'event_name', 'TEST');

var_dump($result);

/*
array(2) {
  'stopped' =>
  bool(false)
  'results' =>
  array(2) {
    [0] =>
    string(4) "TEST"
    [1] =>
    string(4) "test"
  }
}
*/
```

### Stopping event propagation

``` php
\Knlv\events('on', 'event_name', function ($value, $stop) {
    $stop();
    return strtolower($value);
});

\Knlv\events('on', 'event_name', function ($value) {
    return strtoupper($value);
});

$result = \Knlv\events('trigger', 'event_name', 'TEST');

var_dump($result);

/*
array(2) {
  'stopped' =>
  bool(true)
  'results' =>
  array(1) {
    [0] =>
    string(4) "test"
  }
}
*/
```


## License

The php-events is licensed under the GNU GENERAL PUBLIC LICENSE Version 3. See [License File](LICENSE) for more information.