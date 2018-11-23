<?php
namespace RindowTest\Annotation\Entity;

use RindowTest\Annotation\Annotation\Form;

use Rindow\Stdlib\Entity\Entity;
use Rindow\Stdlib\Entity\EntityTrait;

class ProductWithTrait implements Entity
{
    use EntityTrait;
    /** @Max(10) @GeneratedValue **/
    protected $id;
    /** @Min(10) @Column **/
    protected $id2;
    /** @Max(100) @Column(name="stock_value")**/
    protected $stock;
}
/**
* @Form(attributes={"method"="POST"})
*/
class Product2WithTrait implements Entity
{
    use EntityTrait;
    /**
    * @Max(value=10) @GeneratedValue 
    */
    public $id;
    /**
     * @Column
     * #@Max.List({
     *    @Max(value=20,groups={"a"}) 
     *    @Max(value=30,groups={"c"})
     * #})
     */
    public $id2;
    /**
     * @Column
     * @CList({
     *    @Max(value=20,groups={"a"}),
     *    @Max(value=30,groups={"c"})
     * })
     */
    public $stock;
}
