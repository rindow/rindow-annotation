<?php
namespace RindowTest\Annotation\AnnotationManagerTest\Foo70;

use Rindow\{
	Stdlib\PriorityQueue,
	Stdlib\ListCollection as ListCollection1
};
use Rindow\Stdlib\PriorityQueue as PriorityQueue2,
	Rindow\Stdlib\ListCollection as ListCollection2;

use function SKIP\FUNCNAME\{fn_a, fn_b, fn_c};
use const SKIP\CONSTNAME\{ConstA, ConstB, ConstC};

class TestTest
{
	public function func1(int ...$ints):array
	{
		return $ints;
	}
}
