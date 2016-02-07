<?php
/**
 * kanellov/php-events
 * 
 * @link https://github.com/kanellov/php-events for the canonical source repository
 * @copyright Copyright (c) 2004 - 2016 Vassilis Kanellopoulos (http://kanellov.com)
 * @license GNU GPLv3 http://www.gnu.org/licenses/gpl-3.0-standalone.html
 */

namespace KnlvTest;

class EventsTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                // This error code is not included in error_reporting
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }

    protected function setup()
    {
        \Knlv\events('off');
    }

    public function testTriggersEvent()
    {
        $triggered = false;
        \Knlv\events('on', 'event', function () use (&$triggered) {
            $triggered = true;
        });
        \Knlv\events('trigger', 'event');
        $this->assertTrue($triggered);
    }

    public function testTriggerReturnsResultArray()
    {
        \Knlv\events('on', 'event', function () {
            return 1;
        });
        \Knlv\events('on', 'event', function () {
            return 2;
        });
        $result = \Knlv\events('trigger', 'event');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(1, 2),
        ), $result);
    }

    public function testTriggersPassArgumentsInCallables()
    {
        \Knlv\events('on', 'event', function ($stop, $var) {
            return $var;
        });
        $result = \Knlv\events('trigger', 'event', 'value');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array('value'),
        ), $result);
    }

    public function testFirstArgumentInCallableIsStopClosure()
    {
        $isCallable = false;
        \Knlv\events('on', 'event', function ($stop) use (&$isCallable) {
            $isCallable = is_callable($stop);
        });
        \Knlv\events('trigger', 'event');
        $this->assertTrue($isCallable);
    }

    public function testAsteriskEventsTriggeredAlways()
    {
        $triggered = false;
        \Knlv\events('on', 'event', function () {});
        \Knlv\events('on', '*', function () use (&$triggered) {
            $triggered = true;
        });
        \Knlv\events('trigger', 'event');
        $this->assertTrue($triggered);
    }

    public function testPriority()
    {
        \Knlv\events('on', 'event', function () {
            return '1';
        }, 1000);
        \Knlv\events('on', 'event', function () {
            return '2';
        }, 10000);

        $result = \Knlv\events('trigger', 'event');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(2, 1),
        ), $result);
    }

    public function testCallableCanStopPropagation()
    {
        \Knlv\events('on', 'event', function ($stop) {
            $stop();

            return 1;
        });
        \Knlv\events('on', 'event', function ($stop) {
            return 2;
        });
        $result = \Knlv\events('trigger', 'event');
        $this->assertEquals(array(
            'stopped' => true,
            'results' => array(1),
        ), $result);
    }

    public function testOffWithoutArgsClearsAllListeners()
    {
        \Knlv\events('on', 'event1', function () {
            return '1';
        });
        \Knlv\events('on', 'event2', function () {
            return '2';
        });

        $result1 = \Knlv\events('trigger', 'event1');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(1),
        ), $result1);
        $result2 = \Knlv\events('trigger', 'event2');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(2),
        ), $result2);
        \Knlv\events('off');
        $result1 = \Knlv\events('trigger', 'event1');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(),
        ), $result1);
        $result2 = \Knlv\events('trigger', 'event2');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(),
        ), $result2);
    }

    public function testOffWithEventNameClearsAllListenersForGivenEvent()
    {
        \Knlv\events('on', 'event1', function () {
            return '1';
        });
        \Knlv\events('on', 'event1', function () {
            return '2';
        });
        \Knlv\events('on', 'event2', function () {
            return '2';
        });

        $result1 = \Knlv\events('trigger', 'event1');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(1, 2),
        ), $result1);
        $result2 = \Knlv\events('trigger', 'event2');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(2),
        ), $result2);
        \Knlv\events('off', 'event1');
        $result1 = \Knlv\events('trigger', 'event1');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(),
        ), $result1);
        $result2 = \Knlv\events('trigger', 'event2');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(2),
        ), $result2);
    }

    public function testOffOnNonExistingEventListener()
    {
        \Knlv\events('off', 'event');
    }

    public function testOffWithCallableClearsListeners()
    {
        $callable1 = \Knlv\events('on', 'event', function () {
            return 1;
        });
        $callable2 = \Knlv\events('on', 'event', function () {
            return 2;
        });
        \Knlv\events('off', 'event', $callable1);
        \Knlv\events('off', 'event', $callable2);
        $result = \Knlv\events('trigger', 'event');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(),
        ), $result);
    }

    public function testOffWithCallableClearsListener()
    {
        $callable = \Knlv\events('on', 'event', function () {
            return 1;
        });
        \Knlv\events('on', 'event', function () {
            return 2;
        });
        $result1 = \Knlv\events('trigger', 'event');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(1, 2),
        ), $result1);
        \Knlv\events('off', 'event', $callable);
        $result2 = \Knlv\events('trigger', 'event');
        $this->assertEquals(array(
            'stopped' => false,
            'results' => array(2),
        ), $result2);
    }

    public function testTriggersErrorOnWrongAction()
    {
        $this->setExpectedException('ErrorException', 'Invalid action');
        \Knlv\events('not_existing_action');
    }

    public function testTriggersErrorTriggerWithoutEventName()
    {
        $this->setExpectedException('ErrorException', 'Expected event name');
        \Knlv\events('trigger');
    }

    public function testTriggerErrorOnWithoutEventName()
    {
        $this->setExpectedException('ErrorException', 'Expected event name');
        \Knlv\events('on');
    }

    public function testTriggerErrorOnWithoutCallabel()
    {
        $this->setExpectedException('ErrorException', 'Expected callable');
        \Knlv\events('on', 'event');
    }
}
