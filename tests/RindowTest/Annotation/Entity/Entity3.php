<?php
namespace RindowTest\Annotation\Entity;

use RindowTest\Annotation\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="products")
 **/
class Entity3
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue **/
    protected $id;
    /** @ORM\Column(type="string") **/
    protected $name;
}