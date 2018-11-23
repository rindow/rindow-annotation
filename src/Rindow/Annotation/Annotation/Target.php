<?php
namespace Rindow\Annotation\Annotation;

use Rindow\Annotation\NotRegisterAnnotationInterface;

/**
 * @Annotation
 */
class Target implements NotRegisterAnnotationInterface
{
    public $value;

    public $binValue;
}