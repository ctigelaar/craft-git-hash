<?php
namespace Craft;

use Twig_Extension;
use Twig_Filter_Method;
use Twig_Filter_Function;

class ToolsTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'Tools';
    }

    public function getFilters()
    {
        return array(
            'pretty_date' => new Twig_Filter_Method($this, 'getPrettyDate'),
            'youtube_id' => new Twig_Filter_Method($this, 'getYoutubeIdFromUrl'),
            'get_class' => new Twig_Filter_Function('get_class'),
            'method_exists' => new Twig_Filter_Function('method_exists'),
            'print_r' => new Twig_Filter_Method($this, 'print_r'),
            'email_encode' => new Twig_Filter_Method($this, 'emailEncode'),
            'natural_join' => new Twig_Filter_Method($this, 'naturalJoin')
        );
    }


    /**
     * The MIT License (MIT) Copyright (c) 2014 Colin Viebrock
     *
     * @see https://github.com/cviebrock/twig-natural-join
     */
    public function naturalJoin($value, $seperator = '', $final_seperator = '', $oxford = false)
    {
        if ($value instanceof \Traversable)
        {
            $value = iterator_to_array($value, false);
        }
        else
        {
            $value = array_merge($value);
        }

        // one element? just return it
        if (count($value) == 1)
        {
            return reset($value);
        }

        // two elements? only use the final_seperator
        if (count($value) == 2)
        {
            return implode($final_seperator, $value);
        }

        // more than 2 elements, so strip off the last one
        // and join the rest using the normal seperator
        $last = array_pop($value);
        $first = implode($seperator, $value);

        // return the list
        return $first . ($oxford ? $seperator : '') . $final_seperator . $last;
    }

    /**
     * Encode an email address.
     *
     * @return string
     */
    public function emailEncode($emailAddress, $params = array())
    {
        // Is it a valid email address?
        if (! filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            return $emailAddress;
        }

        $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';

        $id = 'e'.rand(1,999999999);
        $key = str_shuffle($character_set);
        $cipher_text = '';

        for ($i = 0; $i < strlen($emailAddress); $i += 1) {
            $cipher_text .= $key[ strpos($character_set,$emailAddress[$i]) ];
        }

        $script = 'var a="'.$key.'";var b=a.split("").sort().join("");var c="'.$cipher_text.'";var d="";var f="";';

        $script.= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));';

        if (!empty($params['label'])) {
            $script.= 'f="'.$params['label'].'";';
            // Unset label
            unset($params['label']);
        }
        else {
            $script.= 'f=d;';
        }

        if (isset($params['link']) && $params['link'] === true) {
            // Unset link
            unset($params['link']);

            // Set attributes
            $attributes = array();
            foreach ($params as $key => $value) {
                $attributes[] = sprintf('%s=\\"%s\\"',
                    $key,
                    $value
                );
            }

            // Add other params as attributes
            $script.= sprintf('document.getElementById("'.$id.'").innerHTML="<a href=\\"mailto:"+d+"\\"%s>"+f+"</a>"',
                count($attributes) ? ' ' . implode(' ', $attributes) : ''
            );
        }
        else {
            $script.= 'document.getElementById("'.$id.'").innerHTML=""+d+""';
        }

        $script = "eval(\"".str_replace(array("\\",'"'),array("\\\\",'\"'), $script)."\")";

        $script = '<script type="text/javascript">/*<![CDATA[*/'.$script.'/*]]>*/</script>';

        $html = '<span id="'.$id.'">[javascript protected email address]</span>'.$script;

        return new \Twig_Markup($html, craft()->templates->getTwig()->getCharset());
    }

    public function print_r($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

    /**
     * Display a date in a much nicer way.
     *
     * @param int $date
     *
     * @return string
     */
    public function getPrettyDate($date)
    {
        $timeStamp            = (int) $date;
        $compareToTimestamp   = (int) time();
        $diff                 = $compareToTimestamp - $timeStamp;
        $dayDiff              = floor($diff / 86400);

        if (is_nan($dayDiff) || $dayDiff < 0)
        {
            return '';
        }

        $vars = array(
            'years' => Craft::t('years'),
            'year' => Craft::t('year'),
            'months' => Craft::t('months'),
            'weeks' => Craft::t('weeks'),
            'week' => Craft::t('week'),
            'days' => Craft::t('days'),
            'day' => Craft::t('day'),
            'hours' => Craft::t('hours'),
            'hour' => Craft::t('hour'),
            'minutes' => Craft::t('minutes'),
            'minute' => Craft::t('minute'),
            'second' => Craft::t('second'),
            'seconds' => Craft::t('seconds'),
            'ago' => Craft::t('ago'),
        );

        if ($dayDiff == 0)
        {
            if ($diff < 60)
            {
                return Craft::t('Just now');
            }
            elseif ($diff < 120)
            {
                $vars['time'] = 1;
                $vars['timeType'] = $vars['minute'];
            }
            elseif ($diff < 3600)
            {
                $vars['time'] = floor($diff / 60);
                $vars['timeType'] = $vars['minutes'];
            }
            elseif ($diff < 7200)
            {
                $vars['time'] = 1;
                $vars['timeType'] = $vars['hour'];
            }
            elseif ($diff < 86400)
            {
                $vars['time'] = floor($diff / 3600);
                $vars['timeType'] = $vars['hours'];
            }
            return Craft::t('{time} {timeType} {ago}', $vars);
        }
        elseif ($dayDiff == 1)
        {
            return Craft::t('Yesterday');
        }
        elseif ($dayDiff < 7)
        {
            $vars['time'] = $dayDiff;
            $vars['timeType'] = $vars['days'];
            return Craft::t('{time} {timeType} {ago}', $vars);
        }
        elseif ($dayDiff == 7)
        {
            $vars['time'] = 1;
            $vars['timeType'] = $vars['week'];
            return Craft::t('{time} {timeType} {ago}', $vars);
        }
        elseif ($dayDiff < (7 * 6))
        {
            $vars['time'] = ceil($dayDiff / 7);
            $vars['timeType'] = $vars['weeks'];
            return Craft::t('{time} {timeType} {ago}', $vars);
        }
        elseif ($dayDiff < 365)
        {
            $vars['time'] = ceil($dayDiff / (365 / 12));
            $vars['timeType'] = $vars['months'];
            return Craft::t('{time} {timeType} {ago}', $vars);
        }
        else
        {
            $years = round($dayDiff / 365);
            $vars['time'] = $years;
            $vars['timeType'] != 1 ? $vars['years'] : $vars['year'];
            return Craft::t('{time} {timeType} {ago}', $vars);
        }
    }

    /**
     * Get a Youtube ID from an URL.
     *
     * @param string $url
     *
     * @return string
     */
    public function getYoutubeIdFromUrl($url) {
        $pattern =
            '%^# Match any youtube URL
            (?:https?://)?  # Optional scheme. Either http or https
            (?:www\.)?      # Optional www subdomain
            (?:             # Group host alternatives
              youtu\.be/    # Either youtu.be,
            | youtube\.com  # or youtube.com
              (?:           # Group path alternatives
                /embed/     # Either /embed/
              | /v/         # or /v/
              | /watch\?v=  # or /watch\?v=
              )             # End path alternatives.
            )               # End host alternatives.
            ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
            $%x';
        $result = preg_match($pattern, $url, $matches);
        if (false !== (bool)$result) {
            return $matches[1];
        }
        return false;
    }
}
