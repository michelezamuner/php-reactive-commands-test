<?php
namespace Update;

class Helper
{
    public static function resolvePath($path)
    {
        $path = str_replace('//', '/', $path);
        $parts = explode('/', $path);
        $out = array();
        foreach ($parts as $part) {
            if ($part == '.') continue;
            if ($part == '..') {
                array_pop($out);
                continue;
            }
            $out[] = $part;
        }
        return implode('/', $out);
    }
}