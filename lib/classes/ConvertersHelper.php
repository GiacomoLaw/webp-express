<?php

namespace WebPExpress;

class ConvertersHelper
{
    public static $defaultConverters = [
        ['converter' => 'cwebp', 'options' => [
            'use-nice' => true,
            'try-common-system-paths' => true,
            'try-supplied-binary-for-os' => true,
            'method' => 6,
            'size-in-percentage' => null,
            'low-memory' => true,
            'command-line-options' => '',
        ]],
        ['converter' => 'vips', 'options' => [
            'smart-subsample' => false,
            'preset' => 'none'
        ]],
        ['converter' => 'imagemagick', 'options' => [
            'use-nice' => true,
        ]],
        ['converter' => 'graphicsmagick', 'options' => [
            'use-nice' => true,
        ]],
        ['converter' => 'wpc'],     // we should not set api-version default - it is handled in the javascript
        ['converter' => 'ewww'],
        ['converter' => 'imagick'],
        ['converter' => 'gmagick'],
        ['converter' => 'gd', 'options' => [
            'skip-pngs' => false,
        ]],
    ];

    public static function getDefaultConverterNames()
    {
        return array_column(self::$defaultConverters, 'converter');
    }

    public static function getConverterNames($converters)
    {
        return array_column(self::normalize($converters), 'converter');
    }

    public static function normalize($converters)
    {
        foreach ($converters as &$converter) {
            if (!isset($converter['converter'])) {
                $converter = ['converter' => $converter];
            }
            if (!isset($converter['options'])) {
                $converter['options'] = [];
            }
        }
        return $converters;
    }

    /**
     *  Those converters in second, but not in first will be appended to first
     */
    public static function mergeConverters($first, $second)
    {
        $namesInFirst = self::getConverterNames($first);
        $second = self::normalize($second);

        foreach ($second as $converter) {
            // migrate9 and this functionality could create two converters.
            // so, for a while, skip graphicsmagick and imagemagick

            if ($converter['converter'] == 'graphicsmagick') {
                if (in_array('gmagickbinary', $namesInFirst)) {
                    continue;
                }
            }
            if ($converter['converter'] == 'imagemagick') {
                if (in_array('imagickbinary', $namesInFirst)) {
                    continue;
                }
            }
            if (!in_array($converter['converter'], $namesInFirst)) {
                $first[] = $converter;
            }
        }
        return $first;
    }

    /**
     * Get converter by id
     *
     * @param  object  $config
     * @return  array|false  converter object
     */
    public static function getConverterById($config, $id) {
        if (!isset($config['converters'])) {
            return false;
        }
        $converters = $config['converters'];

        if (!is_array($converters)) {
            return false;
        }

        foreach ($converters as $c) {
            if (!isset($c['converter'])) {
                continue;
            }
            if ($c['converter'] == $id) {
                return $c;
            }
        }
        return false;
    }

    /**
     * Get working converters.
     *
     * @param  object  $config
     * @return  array
     */
    public static function getWorkingConverters($config) {
        if (!isset($config['converters'])) {
            return [];
        }
        $converters = $config['converters'];

        if (!is_array($converters)) {
            return [];
        }

        $result = [];

        foreach ($converters as $c) {
            if (isset($c['working']) && !$c['working']) {
                continue;
            }
            $result[] = $c;
        }
        return $result;
    }

    public static function getWorkingConverterIds($config)
    {
        $converters = self::getWorkingConverters($config);
        $result = [];
        foreach ($converters as $converter) {
            $result[] = $converter['converter'];
        }
        return $result;
    }

    /**
     * Get working and active converters.
     *
     * @param  object  $config
     * @return  array
     */
    public static function getWorkingAndActiveConverters($config) {
        if (!isset($config['converters'])) {
            return [];
        }
        $converters = $config['converters'];

        if (!is_array($converters)) {
            return [];
        }

        $result = [];

        foreach ($converters as $c) {
            if (isset($c['deactivated']) && $c['deactivated']) {
                continue;
            }
            if (isset($c['working']) && !$c['working']) {
                continue;
            }
            $result[] = $c;
        }
        return $result;
    }

    /**
     * Get converter id by converter object
     *
     * @param  object  $converter
     * @return  string  converter name, or empty string if not set (it should always be set, however)
     */
    public static function getConverterId($converter) {
        if (!isset($converter['converter'])) {
            return '';
        }
        return $converter['converter'];
    }

    /**
     * Get first working and active converter.
     *
     * @param  object  $config
     * @return  object|false
     */
    public static function getFirstWorkingAndActiveConverter($config) {

        $workingConverters = self::getWorkingAndActiveConverters($config);

        if (count($workingConverters) == 0) {
            return false;
        }
        return $workingConverters[0];
    }

    /**
     * Get first working and active converter (name)
     *
     * @param  object  $config
     * @return  string|false    id of converter, or false if no converter is working and active
     */
     public static function getFirstWorkingAndActiveConverterId($config) {
         $c = self::getFirstWorkingAndActiveConverter($config);
         if ($c === false) {
             return false;
         }
         if (!isset($c['converter'])) {
             return false;
         }
         return $c['converter'];
     }

}
