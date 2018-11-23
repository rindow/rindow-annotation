<?php
namespace RindowTest\Annotation\Annotation;

/**
 * The annotated element must be false.
 *
 * @Annotation
 * @Target({ METHOD, FIELD })
 */
class CList
{
    public $message = "list of constraint.";
    public $groups = array();
    public $payload = array();

    /**
    * value list of annotation.
    */
    public $value;
}