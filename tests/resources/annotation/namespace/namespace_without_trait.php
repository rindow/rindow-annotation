<?php
namespace RindowTest\Annotation\AnnotationManagerTest\FooWithoutTrait {
	use Rindow\Stdlib\ListCollection;
	class MyClass
	{
		public function test()
		{
			return new ListCollection();
		}
	}
	class MyClass2
	{
		public function boo()
		{
			return;
		}
	}
}

namespace RindowTest\Annotation\AnnotationManagerTest\BarWithoutTrait {
	use Rindow\Stdlib\PriorityQueue as ListCollection;
	class MyClass
	{
		public function test()
		{
			return new ListCollection();
		}
		public function test2()
		{
			return new ListCollection();
		}
	}
}

namespace {
	use stdClass as ListCollection, Rindow\TestList;
	include __DIR__.'/../../../development/init_autoloader.php';
	$o = new Rindow2AnnotationManagerTest\Foo\MyClass();
	echo get_class($o->test())."\n";
	$o = new Rindow2AnnotationManagerTest\Bar\MyClass();
	echo get_class($o->test())."\n";
	$o = new ListCollection();
	echo get_class($o)."\n";
	$o = new Rindow2AnnotationManagerTest\Bar\Myclass();
	echo get_class($o->test2())."\n";

	$parser = new Rindow\Annotation\NameSpaceExtractor(__FILE__);
	$imports = $parser->getAllImports();
	print_r($imports);
}