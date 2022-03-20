<?php

namespace megabike\forms;

abstract class FormHelper
{

    public static function orderFields($fields, $order)
    {
        if (!$order) {
            return $fields;
        }

        $source = (array)$fields;
        $result = array();
        $map = array();
        foreach ($order as $k => $v) {
            if (is_int($k)) {
                if ($v === '*') {
                    $result += $source;
                    $source = [];
                } elseif (isset($result[$v])) {
                    $f = $result[$v];
                    unset($result[$v]);
                    $result[$v] = $f;
                } elseif (isset($source[$v])) {
                    $f = $source[$v];
                    unset($source[$v]);
                    $result[$v] = $f;
                }
            } elseif (isset($fields[$k])) {
                $map[(string)$v][] = $k;
            }
        }
        $result += $source;
        if ($map) {
            $source = $result;
            $result = [];
            if (isset($map[''])) {
                foreach ($map[''] as $ak) {
                    $result[$ak] = $source[$ak];
                }
            }
            foreach ($source as $k => $f) {
                if (isset($map[$k])) {
                    $result[$k] = $f;
                    foreach ($map[$k] as $ak) {
                        unset($result[$ak]);
                        $result[$ak] = $source[$ak];
                    }
                } else {
                    $result[$k] = $f;
                }
            }
            if (isset($map['*'])) {
                foreach ($map['*'] as $ak) {
                    unset($result[$ak]);
                    $result[$ak] = $source[$ak];
                }
            }
        }
        return $result;
    }

    public static function tableEquals($table1, $table2)
    {
        return self::extractTableName($table1) === self::extractTableName($table2);
    }

    private static function extractTableName($table)
    {
        if (is_array($table)) {
            $v = reset($table);
            $k = key($table);
            if (is_numeric($k)) {
                return self::extractTableName((string)$v);
            } else {
                return self::extractTableName((string)$k);
            }
        } else {
            $m = null;
            $table = trim($table);
            if (preg_match('/^`(.+)`/', $table, $m)) {
                return $m[1];
            } else {
                $t = explode(' ', $table, 2);
                return $t[0];
            }
        }
    }

    public static function processPattern($pattern, $replacements)
    {
        $result = self::processPatternInternal($pattern, 0, $replacements);
        return $result[0];
    }

    private static function processPatternInternal($pattern, $pos, $replacements)
    {
        $p1 = strpos($pattern, '{', $pos);
        $p2 = strpos($pattern, '}', $pos);
        if ($p1 !== false && $p2 === false) {
            return array(substr($pattern, $pos), strlen($pattern), 0);
        }
        if ($p2 === false) {
            $p2 = strlen($pattern);
        }
        if ($p1 === false || $p2 < $p1) {
            $k = substr($pattern, $pos, $p2 - $pos);
            $t = isset($replacements[$k]) ? (string)$replacements[$k] : '';
            $state = $t === '' ? 0 : 1;
            return array($t, $p2 + 1, $state);
        }

        $end = strlen($pattern);
        $buffer = '';
        $state = 0;
        while ($pos < $end) {
            if ($pattern[$pos] === '{') {
                list($t, $p, $s) = self::processPatternInternal($pattern, $pos + 1, $replacements);
                $pos = $p;
                if ($s) {
                    $buffer .= $t;
                    $state = 1;
                }
            } elseif ($pattern[$pos] === '}') {
                return array($buffer, $pos + 1, $state);
            } else {
                $buffer .= $pattern[$pos];
                $pos++;
            }
        }
        return array($buffer, $pos, $state);
    }

}
