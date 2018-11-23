<?php
namespace RindowTest\Annotation\AnnotationAliasTest;

use PHPUnit\Framework\TestCase;
use Rindow\Annotation\AnnotationManager;
use Rindow\Annotation\ElementType;

class Test extends TestCase
{
    static $RINDOW_TEST_RESOURCES;
    public static function setUpBeforeClass()
    {
        self::$RINDOW_TEST_RESOURCES = __DIR__.'/../../resources';
        \Rindow\Stdlib\Cache\CacheFactory::clearFileCache(\Rindow\Stdlib\Cache\CacheFactory::$fileCachePath.'/cache/annotation');
    }

    public static function tearDownAfterClass()
    {
        \Rindow\Stdlib\Cache\CacheFactory::clearFileCache(\Rindow\Stdlib\Cache\CacheFactory::$fileCachePath.'/cache/annotation');
    }

    public function setUp()
    {
    }

    public function testAnnotationAndTargetTag()
    {
        $reader = new AnnotationManager();
        $reader->addAlias('Interop\\Lenient\\Annotation\\Annotation\\Annotation','Annotation');
        $reader->addAlias('Interop\\Lenient\\Annotation\\Annotation\\Target','Target');
        $reader->addAlias('Interop\\Lenient\\Annotation\\Annotation\\Enum','Enum');
        $reader->addAlias('RindowTest\\Annotation\\Alias\\Form','RindowTest\\Annotation\\Annotation\\Form');
        $reader->addAlias('RindowTest\\Annotation\\Alias\\Max','RindowTest\\Annotation\\Annotation\\Max');
        $testAlias = new \RindowTest\Annotation\Entity\TestAlias();
        $ref = new \ReflectionClass($testAlias);

        $annotations = $reader->getClassAnnotations($ref);
        $this->assertCount(1,$annotations);
        $this->assertInstanceof('RindowTest\\Annotation\\Annotation\\Form',$annotations[0]);

        $count = 0;
        foreach ($ref->getMethods() as $methodRef) {
	        $annotations = $reader->getMethodAnnotations($methodRef);
	        $this->assertCount(1,$annotations);
	        $this->assertInstanceof('RindowTest\\Annotation\\Annotation\\Max',$annotations[0]);
	        $count++;
        }
        $this->assertEquals(1,$count);

        $count = 0;
        foreach ($ref->getProperties() as $propetyRef) {
	        $annotations = $reader->getPropertyAnnotations($propetyRef);
	        $this->assertCount(1,$annotations);
	        $this->assertInstanceof('RindowTest\\Annotation\\Annotation\\Max',$annotations[0]);
	        $count++;
        }
        $this->assertEquals(1,$count);
    }
}
