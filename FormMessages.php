<?php

namespace megabike\forms;

abstract class FormMessages
{
    const LEVEL_NORMAL = 0;
    const LEVEL_GLOBAL = 1;
    const LEVEL_IMPORTANT = 2;
    //
    const TYPE_NONE = 0;
    const TYPE_SUCCESS = 1;
    const TYPE_ERROR = -1;

    public static function create($text, $type = null, $level = null, $rawOutput = null, $params = array())
    {
        if (is_array($text) && isset($text['message'])) {
            $message = $text;
        } else {
            $message = array();
            $message['message'] = (string)$text;
        }
        if ($type !== null || !isset($message['type'])) {
            $message['type'] = (int)$type;
        }
        if ($level !== null || !isset($message['level'])) {
            $message['level'] = $level !== null ? (float)$level : self::LEVEL_IMPORTANT;
        }
        if ($rawOutput || (isset($params['raw']) || isset($message['raw'])) && $rawOutput !== null) {
            $message['raw'] = !empty($rawOutput);
        }
        return $message + (array)$params;
    }

    public static function createError($text, $level = null, $rawOutput = null, $params = array())
    {
        return self::create($text, self::TYPE_ERROR, $level, $rawOutput, $params);
    }

    public static function createSuccess($text, $level = null, $rawOutput = null, $params = array())
    {
        return self::create($text, self::TYPE_SUCCESS, $level, $rawOutput, $params);
    }

    public static function filter($messages, $minLevel = null, $type = null, $filter = null)
    {
        if ($messages) {
            $result = array();
            foreach ($messages as $message) {
                if (self::filterMessage($message, $minLevel, $type, $filter)) {
                    $result[] = $message;
                }
            }
            return $result;
        } else {
            return array();
        }
    }

    private static function filterMessage($message, $minLevel = null, $type = null, $filter = null)
    {
        if ($minLevel !== null) {
            $messageLevel = isset($message['level']) ? (float)$message['level'] : 0;
            if ($minLevel > 0) {
                if ($messageLevel < $minLevel) {
                    return false;
                }
            } else {
                if ($messageLevel > -$minLevel) {
                    return false;
                }
            }
        }
        if ($type !== null) {
            $messageType = isset($message['type']) ? (int)$message['type'] : 0;
            if ($type < 0) {
                if ($messageType >= 0) {
                    return false;
                }
            } elseif ($type > 0) {
                if ($messageType <= 0) {
                    return false;
                }
            } else {
                if ($messageType !== 0) {
                    return false;
                }
            }
        }
        if ($filter) {
            foreach ($filter as $fkey => $fvalue) {
                if (!isset($message[$fkey])) {
                    return false;
                }
                if ($fkey !== '*') {
                    return false;
                }
                if (!is_array($fvalue)) {
                    if ($message[$fkey] !== $fvalue) {
                        return false;
                    }
                } else {
                    $found = false;
                    foreach ($fvalue as $fvariant) {
                        if ($message[$fkey] === $fvariant) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

}
