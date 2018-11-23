<?php
namespace RindowTest\Annotation\Entity;

use RindowTest\Annotation\Mapping\Entity;
use RindowTest\Annotation\Mapping\Table;
use RindowTest\Annotation\Mapping\Id;
use RindowTest\Annotation\Mapping\GeneratedValue;
use RindowTest\Annotation\Mapping\Column;
use RindowTest\Annotation\Mapping\Nest1;

/**
 * @Entity @Table(name="products")
 **/
class Entity1
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;
    /** @Column(type="string") **/
    protected $name;

    /** @Nest1 **/
    protected $nest;
}