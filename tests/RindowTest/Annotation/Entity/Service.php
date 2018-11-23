<?php
namespace RindowTest\Annotation\Entity;

use RindowTest\Annotation\Mapping\Entity;
use RindowTest\Annotation\Mapping\Table;
use RindowTest\Annotation\Mapping\Id;
use RindowTest\Annotation\Mapping\Inject;

/**
 * @Entity @Table(name="products")
 **/
class Service
{
    /** @Id **/
    protected $id;
    protected $manager;

    /** @Inject() **/
    public function setManager($manager)
    {

    }
}