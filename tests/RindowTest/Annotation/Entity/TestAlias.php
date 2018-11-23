<?php
namespace RindowTest\Annotation\Entity;

use RindowTest\Annotation\Alias\Form;
use RindowTest\Annotation\Alias\Max;

/** @Form **/
class TestAlias
{
    /** @Max **/
    public $number;

    /** @Max **/
    public function sum()
    {
    }
}