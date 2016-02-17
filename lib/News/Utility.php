<?php

namespace News;

class Utility {

    public static function getRealFriendlyName( $string ) {

        $string = \Pimcore\File::getValidFilename( $string );

        $string = str_replace(array('ä','ü','ö','Ä','Ü','Ö','ß'), array('ae','ue','oe','Ae','Ue','Oe','ss'), $string );

        return  preg_replace('/-{2,}/','-', strtolower( $string ));
    }
}