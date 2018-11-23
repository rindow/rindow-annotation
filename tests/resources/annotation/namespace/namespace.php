<?php
namespace RindowTest\Annotation\AnnotationManagerTest\Foo {
	use Rindow\Stdlib\ListCollection;
	class MyClass
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
	class MyClass2
	{
		public function boo()
		{
			return;
		}
	}
}

namespace RindowTest\Annotation\AnnotationManagerTest\Bar {
	use Rindow\Stdlib\PriorityQueue as ListCollection;
	class MyClass
	{
        use \RindowTest\Annotation\AnnotationManagerTest\Foo\TestTrait;
		public function test()
		{
			return new ListCollection();
		}
	}
}

namespace {
	use stdClass as ListCollection, Rindow\TestList;
	include __DIR__.'/../../../development/init_autoloader.php';
	$o = new RindowTest\Annotation\AnnotationManagerTest\Foo\MyClass();
	echo get_class($o->test())."\n";
	$o = new RindowTest\Annotation\AnnotationManagerTest\Bar\MyClass();
	echo get_class($o->test())."\n";
	$o = new ListCollection();
	echo get_class($o)."\n";
	$o = new RindowTest\Annotation\AnnotationManagerTest\Bar\Myclass();
	echo get_class($o->test2())."\n";

	$parser = new Rindow\Annotation\NameSpaceExtractor(__FILE__);
	$imports = $parser->getAllImports();
	print_r($imports);
}