<?php
namespace RindowTest\Annotation\Entity;

use RindowTest\Annotation;

/**
 * @Annotation\Mapping\Entity @Annotation\Mapping\Table(name="products")
 **/
class Entity2
{
    /** @Annotation\Mapping\Id @Annotation\Mapping\Column(type="integer") @Annotation\Mapping\GeneratedValue **/
    protected $id;
    /** @Annotation\Mapping\Column(type="string") **/
    protected $name;
}