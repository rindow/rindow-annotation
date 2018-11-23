<?php
namespace RindowTest\Annotation\Annotation;

/**
 * @Annotation
 * @Target({ TYPE })
 */
class Form
{
	public $value;
	/**
	 * @Enum({"form"})
	 */
    public $type = 'form';
    
	public $attributes;
    public $hasErrors;
}