<?php

namespace News;

class Utility
{
    /**
     * @param $string
     *
     * @return mixed
     */
    public static function getRealFriendlyName($string)
    {
        $string = \Pimcore\File::getValidFilename($string);
        $string = str_replace(['ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö', 'ß'], ['ae', 'ue', 'oe', 'Ae', 'Ue', 'Oe', 'ss'], $string);

        return preg_replace('/-{2,}/', '-', strtolower($string));
    }
}