<?php
namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        return $this->computeCacheDir();
    }

    private function computeCacheDir(): string
    {
        // For Docker performance
        if ($this->getEnvironment() === 'test' || $this->getEnvironment() === 'dev') {
            return '/tmp/'.$this->environment;
        } else {
            return $this->getProjectDir().'/var/cache/'.$this->environment;
        }
    }
}
?>