<?php

namespace CacheTool\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface ContainerAwareInterface {

  public function setContainer(?ContainerInterface $container);

}
