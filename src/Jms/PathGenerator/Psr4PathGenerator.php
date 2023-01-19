<?php

namespace GoetasWebservices\Xsd\XsdToPhp\Jms\PathGenerator;

use GoetasWebservices\Xsd\XsdToPhp\PathGenerator\PathGeneratorException;
use GoetasWebservices\Xsd\XsdToPhp\PathGenerator\Psr4PathGenerator as Psr4PathGeneratorBase;

class Psr4PathGenerator extends Psr4PathGeneratorBase implements PathGenerator
{
    private $paths = [];

    public function getPath($yaml)
    {
        $ns = key($yaml);

        foreach ($this->namespaces as $namespace => $dir) {
            $pos = strpos($ns, $namespace);

            if ($pos === 0) {
                if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
                    throw new PathGeneratorException("Can't create the folder '$dir'");
                }
                $f = trim(strtr(substr($ns, strlen($namespace)), '\\/', '..'), '.');
                $path = $dir . '/' . $f . '.yml';

                // Make sure that we generate a unique case-insensitive path.
                // Otherwise we'll into problems on case-insensitive file systems.
                $normalizedPath = strtolower($path);
                if (isset($this->paths[$normalizedPath])) {
                    for ($i = 0; $i < 100; $i++) {
                        $uniquePath = preg_replace('@\.yml$@', '-' . $i . '.yml', $path);
                        if (!isset($this->paths[strtolower($uniquePath)])) {
                            $path = $uniquePath;
                            $normalizedPath = strtolower($path);
                            break;
                        }
                    }
                }
                $this->paths[$normalizedPath] = $path;

                return $path;
            }
        }
        throw new PathGeneratorException("Unable to determine location to save JMS metadata for class '$ns'");
    }
}
