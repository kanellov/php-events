<?php
/**
 * kanellov/php-events
 * 
 * @link https://github.com/kanellov/php-events for the canonical source repository
 * @copyright Copyright (c) 2004 - 2016 Vassilis Kanellopoulos (http://kanellov.com)
 * @license GNU GPLv3 http://www.gnu.org/licenses/gpl-3.0-standalone.html
 */

namespace Knlv;

function events($action)
{
    static $listeners = array();
    static $actions   = array('trigger', 'on', 'off');

    if (!in_array($action, $actions)) {
        trigger_error('Invalid action', E_USER_ERROR);
    }

    $args = array_slice(func_get_args(), 1);

    if (0 === strcasecmp('trigger', $action)) {
        if (!isset($args[0])) {
            trigger_error('Expected event name', E_USER_ERROR);
        }
        $event          = array_shift($args);
        $results        = array();
        $stopped        = false;
        $eventListeners = array_merge_recursive(
            isset($listeners[$event]) ? $listeners[$event] : array(),
            isset($listeners['*']) ? $listeners['*'] : array()
        );
        krsort($eventListeners, SORT_NUMERIC);

        array_push($args, function () use (&$stopped) {
            $stopped = true;
        });
        foreach ($eventListeners as $listenersByPriority) {
            foreach ($listenersByPriority as $listener) {
                $results[] = call_user_func_array($listener, $args);
                if ($stopped) {
                    break;
                }
            }
            if ($stopped) {
                break;
            }
        }

        return array('stopped' => $stopped, 'results' => $results);
    }

    if (0 === strcasecmp('on', $action)) {
        if (!isset($args[0])) {
            trigger_error('Expected event name', E_USER_ERROR);
        }
        if (!isset($args[1]) || !is_callable($args[1])) {
            trigger_error('Expected callable', E_USER_ERROR);
        }
        $event                                   = array_shift($args);
        $callable                                = array_shift($args);
        $priority                                = isset($args[0]) ? (int) $args[0] : 1;
        $listeners[$event][$priority . '.0'][]   = $callable;

        return $callable;
    }

    if (0 === strcasecmp('off', $action)) {

        // no event name provided, clear all events
        if (!isset($args[0])) {
            $listeners = array();

            return;
        }

        // event name provided no listeners found for this event
        // nothing to do
        if (!isset($listeners[$args[0]])) {
            return;
        }

        // no listener provided clear all listeners for event
        if (!isset($args[1])) {
            unset($listeners[$args[0]]);

            return;
        }

        foreach ($listeners[$args[0]] as $priority => $eventListeners) {
            foreach ($eventListeners as $index => $listener) {
                if ($listener !== $args[1]) {
                    continue;
                }

                unset($listeners[$args[0]][$priority][$index]);

                if (empty($listeners[$args[0]][$priority])) {
                    unset($listeners[$args[0]][$priority]);
                    break;
                }
            }
            if (empty($listeners[$args[0]])) {
                unset($listeners[$args[0]]);
                break;
            }
        }
    }
}
