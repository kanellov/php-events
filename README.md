# php-events

|master|develop|
|------|-------|
|[![Build Status](https://travis-ci.org/kanellov/php-events.svg?branch=master)](https://travis-ci.org/kanellov/php-events)|[![Build Status](https://travis-ci.org/kanellov/php-events.svg?branch=develop)](https://travis-ci.org/kanellov/php-events)|

A simple events manager function

# Requirements

- php >= 5.3

# Installation

``` terminal
$ composer require kanellov/php-events
```

# Usage

- `\Knlv\events('on', string $event_name, callable $listener);` registers a `$listener` for event `$event_name`
- `\Knlv\events('on', '*', callable $listener)` registers a `$listener` for all events
- `\Knlv\events('trigger', string $event_name [, mixed $...]);` triggers the `$event_name` event and passes args.
- `\Knlv\events('off');` detaches all listeners
- `\Knlv\events('off', $event_name)` detaches all listeners for event `$event_name`
- `\Knlv\events('off', $event_name, $listener)` detaches a certain `$listener` for event `$event_name`

``` php

\Knlv\events('on', 'event_name', function ($stop, $value) {
    return strtolower($value);
});

$result = \Knlv\events('trigger', 'event_name', 'TEST');

var_dump($result);

/*
array(2) {
  'stopped' =>
  bool(false)
  'results' =>
  array(1) {
    [0] =>
    string(4) "test"
  }
}
*/

```