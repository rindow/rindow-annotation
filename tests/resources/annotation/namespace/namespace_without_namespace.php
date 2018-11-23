<?php
use Rindow\Stdlib\ListCollection;
use Rindow\Stdlib\PriorityQueue as ListCollection2;
use stdClass as ListCollection3, Rindow\TestList;

class RindowTest_Annotation_AnnotationManagerTest_FooWithoutNamespace_MyClass
{
	public function test()
	{
		return new ListCollection();
	}
}
trait TestTrait
{
	public function test2()
	{
		return new ListCollection();
	}
}
class RindowTest_Annotation_AnnotationManagerTest_FooWithoutNamespace_MyClass2
{
	public function boo()
	{
		return;
	}
}

class RindowTest_Annotation_AnnotationManagerTest_BarWithoutNamespace_MyClass
{
    use \RindowTest\Annotation\AnnotationManagerTest\Foo\TestTrait;
	public function test()
	{
		return new ListCollection2();
	}
}

include __DIR__.'/../../../development/init_autoloader.php';
$o = new RindowTest\Annotation\AnnotationManagerTest\Foo\MyClass();
echo get_class($o->test())."\n";
$o = new RindowTest\Annotation\AnnotationManagerTest\Bar\MyClass();
echo get_class($o->test())."\n";
$o = new ListCollection3();
echo get_class($o)."\n";
$o = new RindowTest\Annotation\AnnotationManagerTest\Bar\Myclass();
echo get_class($o->test2())."\n";
$parser = new Rindow\Annotation\NameSpaceExtractor(__FILE__);
$imports = $parser->getAllImports();
print_r($imports);
