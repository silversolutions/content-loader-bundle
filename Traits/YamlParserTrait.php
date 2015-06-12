<?php

namespace Siso\Bundle\ContentLoaderBundle\Traits;

use Symfony\Component\Yaml\Yaml;

/**
 * Trait for yaml parsing
  */
trait YamlParserTrait
{
    /**
     * Load yaml from file
     *
     * @param string $path
     * @return array
     */
    public function loadYamlFile($path)
    {
        $yamlText = file_get_contents($path);
        return Yaml::parse($yamlText);
    }
}