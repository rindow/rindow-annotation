<?php
namespace Rindow\Annotation\Annotation;

use Rindow\Annotation\NotRegisterAnnotationInterface;

/**
 * @Annotation
 */
class Enum implements NotRegisterAnnotationInterface
{
    public $value;

    public $hashValue;
}