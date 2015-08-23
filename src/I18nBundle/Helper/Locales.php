<?php
namespace TheRat\OmCms\I18nBundle\Helper;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class Locales
{
    public static function getDbLocalesFromFile($filename)
    {
        $fs = new Filesystem();
        $result = [];
        if ($fs->exists($filename)) {
            $parser = new Parser();
            $value = $parser->parse(file_get_contents($filename));
            $result = $value['parameters']['om_cms_i18n.locale.aliases'];
        }
        return $result;
    }
}
