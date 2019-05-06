<?php
namespace RindowTest\Annotation\AnnotationManagerTest;

use PHPUnit\Framework\TestCase;
use Rindow\Stdlib\Entity\AbstractEntity;
use ReflectionClass;

// Test Target Classes
use Rindow\Annotation\AnnotationManager;
use Rindow\Annotation\ElementType;
use Rindow\Annotation\Annotation\TargetProvider;
use Rindow\Annotation\NameSpaceExtractor;

/**
 * @Annotation
 * @Target(FIELD,ANNOTATION_TYPE)
 */
class Test
{
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 * @Test(1)
 */
class Test2
{
    public $arg1;
    public $arg2;
}
/**
 * @Target(TYPE)
 */
class Test3
{
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 */
class Test4
{
    /**
     * @Enum({"ABC","XYZ"})
     */
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 */
class Test5
{
    /**
     * @Enum({"LARGE","SMALL"})
     */
    public $type;
}
/**
 * @Annotation
 * @Target(XYZ)
 */
class Test6
{
    public $value;
}
/**
 * @Annotation
 * @Target
 */
class Test7
{
    public $value;
}
/**
 * @Annotation
 * @Target()
 */
class Test8
{
    public $value;
}
/**
 * @Annotation
 */
class Test9
{
    /**
     * @Target()
     */
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 */
class Test10
{
    /**
     * @Enum
     */
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 */
class Test11
{
    /**
     * @Enum()
     */
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 * @Enum({"ABC","XYZ"})
 */
class Test12
{
    public $value;
}
/**
 * @Test3
 */
class TestEntityIncludeNotAnnotation {
    public $id;
}
class Product extends AbstractEntity
{
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
class Product2 extends AbstractEntity
{
    /**
    * @Max(value=10) @GeneratedValue 
    */
    public $id;
    /**
     * Duplicate Annotation
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


class ManagerTest extends TestCase
{
    static $RINDOW_TEST_RESOURCES;
    public static function setUpBeforeClass()
    {
        self::$RINDOW_TEST_RESOURCES = __DIR__.'/../../resources';
    }

    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {
    }

    public function testAnnotationAndTargetTag()
    {
        $docComment = <<<EOD
/**
 * @Test("ABC")
 * @Test2(arg1="DEF",arg2="GHI")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(2,count($annotations));
        $this->assertEquals(__NAMESPACE__.'\Test',get_class($annotations[__NAMESPACE__.'\Test']));
        $this->assertEquals("ABC",$annotations[__NAMESPACE__.'\Test']->value);

        $metaData = $reader->getMetaData(__NAMESPACE__.'\Test');
        $this->assertEquals(5,count(get_object_vars($metaData)));
        $this->assertEquals(__NAMESPACE__.'\Test',$metaData->className);
        $this->assertEquals('Rindow\Annotation\Annotation\Annotation',get_class($metaData->classAnnotations[0]));
        $this->assertEquals('Rindow\Annotation\Annotation\Target',get_class($metaData->classAnnotations[1]));
        $this->assertEquals(
            (TargetProvider::TARGET_FIELD|
             TargetProvider::TARGET_ANNOTATION_TYPE),
            $metaData->classAnnotations[1]->binValue);
        $this->assertFalse($metaData->hasConstructor);
        $this->assertEquals(array('FIELD','ANNOTATION_TYPE'),$metaData->classAnnotations[1]->value);
        $this->assertNull($metaData->fieldAnnotations);
        $this->assertNull($metaData->methodAnnotations);

        $this->assertEquals(__NAMESPACE__.'\Test2',get_class($annotations[__NAMESPACE__.'\Test2']));
        $this->assertEquals("DEF",$annotations[__NAMESPACE__.'\Test2']->arg1);
        $this->assertEquals("GHI",$annotations[__NAMESPACE__.'\Test2']->arg2);

        $metaData = $reader->getMetaData(__NAMESPACE__.'\Test2');
        $this->assertEquals(5,count(get_object_vars($metaData)));
        $this->assertEquals(__NAMESPACE__.'\Test2',$metaData->className);
        $this->assertEquals(
            (TargetProvider::TARGET_FIELD),
            $metaData->classAnnotations[1]->binValue);
        $this->assertFalse($metaData->hasConstructor);
        $this->assertEquals(3,count($metaData->classAnnotations));
        $this->assertEquals(__NAMESPACE__.'\Test',get_class($metaData->classAnnotations[2]));
        $this->assertEquals(1,$metaData->classAnnotations[2]->value);
        $this->assertNull($metaData->fieldAnnotations);
        $this->assertNull($metaData->methodAnnotations);
    }

    public function testListParam()
    {
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);

        $docComment = '/** @Test("ABC","XYZ") */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test',get_class($annotations[__NAMESPACE__.'\Test']));
        $this->assertEquals(array("ABC","XYZ"),$annotations[__NAMESPACE__.'\Test']->value);

        $docComment = '/** @Test(123,456) */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test',get_class($annotations[__NAMESPACE__.'\Test']));
        $this->assertEquals(array(123,456),$annotations[__NAMESPACE__.'\Test']->value);

        $docComment = '/** @Test({123,456}) */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test',get_class($annotations[__NAMESPACE__.'\Test']));
        $this->assertEquals(array(123,456),$annotations[__NAMESPACE__.'\Test']->value);

        $docComment = '/** @Test({123}) */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test',get_class($annotations[__NAMESPACE__.'\Test']));
        $this->assertEquals(array(123),$annotations[__NAMESPACE__.'\Test']->value);

        $docComment = '/** @Test2(arg1=1,arg2=2) */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test2',get_class($annotations[__NAMESPACE__.'\Test2']));
        $this->assertEquals(1,$annotations[__NAMESPACE__.'\Test2']->arg1);
        $this->assertEquals(2,$annotations[__NAMESPACE__.'\Test2']->arg2);

        $docComment = '/** @Test({@Test(1),@Test(2)}) */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test',get_class($annotations[__NAMESPACE__.'\Test']));
        $this->assertEquals(1,$annotations[__NAMESPACE__.'\Test']->value[0]->value);
        $this->assertEquals(2,$annotations[__NAMESPACE__.'\Test']->value[1]->value);

        $docComment = '/** @Test({@Test(3)}) */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test',get_class($annotations[__NAMESPACE__.'\Test']));
        $this->assertEquals(3,$annotations[__NAMESPACE__.'\Test']->value[0]->value);
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage the class is not annotation class.: RindowTest\Annotation\AnnotationManagerTest\Test3 in Test\Test::$id: filename.php
     */
    public function testAnnotationTagError()
    {
        $docComment = <<<EOD
/**
 * @Test3("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 123,
        );

        $annotations = $parser->searchAnnotation($docComment,$location);

        $this->assertEquals(__NAMESPACE__.'\Test',get_class($annotations[0]));
        $this->assertEquals("ABC",$annotations[0]->value);
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage the annotation "@RindowTest\Annotation\AnnotationManagerTest\Test" do not allow to TYPE in Test\Test::$id: filename.php
     */
    public function testTargetTagErrorNotAllow()
    {
        $docComment = <<<EOD
/**
 * @Test("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 100);
        $annotations = $parser->searchAnnotation($docComment,$location);

        //var_dump($reader->getMetaData(__NAMESPACE__.'\Test'));

        $this->assertEquals(__NAMESPACE__.'\Test',get_class($annotations[0]));
        $this->assertEquals("ABC",$annotations[0]->value);
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage the paremeter "XYZ" is a invalid argument for the @Target in RindowTest\Annotation\AnnotationManagerTest\Test6:
     */
    public function testTargetTagErrorInvalidElementType()
    {
        $docComment = <<<EOD
/**
 * @Test6("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);
        $annotations = $parser->searchAnnotation($docComment,$location);

    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Target dose not have element types in RindowTest\Annotation\AnnotationManagerTest\Test7:
     */
    public function testTargetTagErrorHasNull()
    {
        $docComment = <<<EOD
/**
 * @Test7("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);
        $annotations = $parser->searchAnnotation($docComment,$location);

    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Target dose not have element types in RindowTest\Annotation\AnnotationManagerTest\Test8:
     */
    public function testTargetTagErrorHasEmptyArray()
    {
        $docComment = <<<EOD
/**
 * @Test8("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Target must be placed as ANNOTAION_TYPE in RindowTest\Annotation\AnnotationManagerTest\Test9::$value:
     */
    public function testTargetTagErrorInFeild()
    {
        $docComment = <<<EOD
/**
 * @Test9("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);
        $annotations = $parser->searchAnnotation($docComment,$location);

    }

    public function testEnumTag()
    {
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);

        $docComment = '/** @Test4("ABC") */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test4',get_class($annotations[__NAMESPACE__.'\Test4']));
        $this->assertEquals("ABC",$annotations[__NAMESPACE__.'\Test4']->value);

        $docComment = '/** @Test4("XYZ") */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test4',get_class($annotations[__NAMESPACE__.'\Test4']));
        $this->assertEquals("XYZ",$annotations[__NAMESPACE__.'\Test4']->value);

        $docComment = '/** @Test5(type="LARGE") */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test5',get_class($annotations[__NAMESPACE__.'\Test5']));
        $this->assertEquals("LARGE",$annotations[__NAMESPACE__.'\Test5']->type);

        $docComment = '/** @Test5(type="SMALL") */';
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(__NAMESPACE__.'\Test5',get_class($annotations[__NAMESPACE__.'\Test5']));
        $this->assertEquals("SMALL",$annotations[__NAMESPACE__.'\Test5']->type);
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage a value "DEF" is not allowed for the field "value" of annotation @RindowTest\Annotation\AnnotationManagerTest\Test4 in Test\Test::$id: filename.php
     */
    public function testEnumTagErrorNotArrowToValue()
    {
        $docComment = <<<EOD
/**
 * @Test4("DEF")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage a value "DEF" is not allowed for the field "type" of annotation @RindowTest\Annotation\AnnotationManagerTest\Test5 in Test\Test::$id: filename.php
     */
    public function testEnumTagErrorNotArrowToArgument()
    {
        $docComment = <<<EOD
/**
 * @Test5(type="DEF")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Enum dose not have enumulated values in RindowTest\Annotation\AnnotationManagerTest\Test10::$value:
     */
    public function testEnumTagErrorHasNull()
    {
        $docComment = <<<EOD
/**
 * @Test10("DEF")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Enum dose not have enumulated values in RindowTest\Annotation\AnnotationManagerTest\Test11::$value:
     */
    public function testEnumTagErrorHasEmptyArray()
    {
        $docComment = <<<EOD
/**
 * @Test11("DEF")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Enum must be placed as FILED in RindowTest\Annotation\AnnotationManagerTest\Test12:
     */
    public function testEnumTagErrorInANNOTATIONTYPE()
    {
        $docComment = <<<EOD
/**
 * @Test12("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 1);
        $annotations = $parser->searchAnnotation($docComment,$location);

    }

    public function testFormStyleEntity()
    {
        $reader = new AnnotationManager();
        $reader->addNameSpace('RindowTest\Annotation\Mapping');
        $reader->addNameSpace('RindowTest\Annotation\Annotation');
        $classRef = new ReflectionClass(__NAMESPACE__.'\Product2');

        $annotations['__CLASS__'] = $reader->getClassAnnotations($classRef);
        $this->assertEquals(1,count($annotations['__CLASS__']));
        $this->assertEquals('RindowTest\Annotation\Annotation\Form',get_class($annotations['__CLASS__'][0]));
        $propertyRefs = $classRef->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $annotations[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($annotations['id']));
        $this->assertEquals('RindowTest\Annotation\Annotation\Max',get_class($annotations['id'][0]));
        $this->assertEquals('RindowTest\Annotation\Mapping\GeneratedValue',get_class($annotations['id'][1]));
        $this->assertEquals(2,count($annotations['id2']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',get_class($annotations['id2'][0]));
        $this->assertEquals('RindowTest\Annotation\Annotation\Max',get_class($annotations['id2'][1]));
        $this->assertEquals(30,$annotations['id2'][1]->value);
        $this->assertEquals(2,count($annotations['stock']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',get_class($annotations['stock'][0]));
        $this->assertEquals('RindowTest\Annotation\Annotation\CList',get_class($annotations['stock'][1]));
        $this->assertEquals(2,count($annotations['stock'][1]->value));
        $this->assertEquals('RindowTest\Annotation\Annotation\Max',get_class($annotations['stock'][1]->value[0]));
        $this->assertEquals('RindowTest\Annotation\Annotation\Max',get_class($annotations['stock'][1]->value[1]));
    }

    /**
     * @requires PHP 5.4.0
     */
    public function testFormStyleWithTrait()
    {
        require_once self::$RINDOW_TEST_RESOURCES.'/annotation/trait/class_with_trait.php';
        $reader = new AnnotationManager();
        $reader->addNameSpace('RindowTest\Annotation\Mapping');
        $reader->addNameSpace('RindowTest\Annotation\Annotation');
        $classRef = new ReflectionClass('RindowTest\Annotation\Entity\Product2WithTrait');

        $annotations['__CLASS__'] = $reader->getClassAnnotations($classRef);
        $this->assertEquals(1,count($annotations['__CLASS__']));
        $this->assertEquals('RindowTest\Annotation\Annotation\Form',get_class($annotations['__CLASS__'][0]));
        $propertyRefs = $classRef->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $annotations[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($annotations['id']));
        $this->assertEquals('RindowTest\Annotation\Annotation\Max',get_class($annotations['id'][0]));
        $this->assertEquals('RindowTest\Annotation\Mapping\GeneratedValue',get_class($annotations['id'][1]));
        $this->assertEquals(2,count($annotations['id2']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',get_class($annotations['id2'][0]));
        $this->assertEquals('RindowTest\Annotation\Annotation\Max',get_class($annotations['id2'][1]));
        $this->assertEquals(30,$annotations['id2'][1]->value);
        $this->assertEquals(2,count($annotations['stock']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',get_class($annotations['stock'][0]));
        $this->assertEquals('RindowTest\Annotation\Annotation\CList',get_class($annotations['stock'][1]));
        $this->assertEquals(2,count($annotations['stock'][1]->value));
        $this->assertEquals('RindowTest\Annotation\Annotation\Max',get_class($annotations['stock'][1]->value[0]));
        $this->assertEquals('RindowTest\Annotation\Annotation\Max',get_class($annotations['stock'][1]->value[1]));
    }

    /**
     * @expectedException        Rindow\Annotation\Exception\DomainException
     * @expectedExceptionMessage the class is not annotation class.: RindowTest\Annotation\AnnotationManagerTest\Test3 in RindowTest\Annotation\AnnotationManagerTest\TestEntityIncludeNotAnnotation:
     */
    public function testAnnotationTagErrorIncluding()
    {
        $reader = new AnnotationManager();
        $reader->addNameSpace(__NAMESPACE__);
        $classRef = new ReflectionClass(__NAMESPACE__.'\TestEntityIncludeNotAnnotation');
        $annotations = $reader->getClassAnnotations($classRef);
    }

    /**
     * @requires PHP 5.4.0
     */
    public function testNamespaceExtractor54()
    {
        $parser = new NameSpaceExtractor(self::$RINDOW_TEST_RESOURCES.'/annotation/namespace/namespace.php');
        $imports = $parser->getAllImports();
        $this->assertEquals(3,count($imports));
        $this->assertEquals(1,count($imports[__NAMESPACE__.'\Foo']));
        $this->assertEquals(1,count($imports[__NAMESPACE__.'\Bar']));
        $this->assertEquals(2,count($imports['__TOPLEVEL__']));
        $this->assertEquals('Rindow\Stdlib\ListCollection',$imports[__NAMESPACE__.'\Foo']['ListCollection']);
        $this->assertEquals('Rindow\Stdlib\PriorityQueue',$imports[__NAMESPACE__.'\Bar']['ListCollection']);
        $this->assertEquals('stdClass',$imports['__TOPLEVEL__']['ListCollection']);
        $this->assertEquals('Rindow\TestList',$imports['__TOPLEVEL__']['TestList']);
        $classes = $parser->getAllClass();
        $this->assertEquals(__NAMESPACE__.'\Foo\MyClass',$classes[0]);
        $this->assertEquals(__NAMESPACE__.'\Foo\MyClass2',$classes[1]);
        $this->assertEquals(__NAMESPACE__.'\Bar\MyClass',$classes[2]);
    }

    /**
     * @requires PHP 5.5.0
     */
    public function testNamespaceExtractor55()
    {
        $parser = new NameSpaceExtractor(self::$RINDOW_TEST_RESOURCES.'/annotation/namespace/namespace55.php');
        $imports = $parser->getAllImports();
        $this->assertEquals(3,count($imports));
        $this->assertEquals(1,count($imports[__NAMESPACE__.'\Foo55']));
        $this->assertEquals(1,count($imports[__NAMESPACE__.'\Bar55']));
        $this->assertEquals(2,count($imports['__TOPLEVEL__']));
        $this->assertEquals('Rindow\Stdlib\ListCollection',$imports[__NAMESPACE__.'\Foo55']['ListCollection']);
        $this->assertEquals('Rindow\Stdlib\PriorityQueue',$imports[__NAMESPACE__.'\Bar55']['ListCollection']);
        $this->assertEquals('stdClass',$imports['__TOPLEVEL__']['ListCollection']);
        $this->assertEquals('Rindow\TestList',$imports['__TOPLEVEL__']['TestList']);
        $classes = $parser->getAllClass();
        $this->assertEquals(__NAMESPACE__.'\Foo55\MyClass',$classes[0]);
        $this->assertEquals(__NAMESPACE__.'\Foo55\MyClass2',$classes[1]);
        $this->assertEquals(__NAMESPACE__.'\Bar55\MyClass',$classes[2]);
    }

    /**
     * @requires PHP 7.0.0
     */
    public function testNamespaceExtractor70()
    {
        $parser = new NameSpaceExtractor(self::$RINDOW_TEST_RESOURCES.'/annotation/namespace/namespace70.php');
        $imports = $parser->getAllImports();
        $this->assertCount(1,$imports);
        $this->assertCount(4,$imports[__NAMESPACE__.'\Foo70']);
        $this->assertEquals('Rindow\Stdlib\PriorityQueue', $imports[__NAMESPACE__.'\Foo70']['PriorityQueue']);
        $this->assertEquals('Rindow\Stdlib\ListCollection',$imports[__NAMESPACE__.'\Foo70']['ListCollection1']);
        $this->assertEquals('Rindow\Stdlib\PriorityQueue', $imports[__NAMESPACE__.'\Foo70']['PriorityQueue2']);
        $this->assertEquals('Rindow\Stdlib\ListCollection',$imports[__NAMESPACE__.'\Foo70']['ListCollection2']);
        $classes = $parser->getAllClass();
        $this->assertCount(1,$classes);
        $this->assertEquals(__NAMESPACE__.'\Foo70\TestTest',$classes[0]);
    }

    /**
     * @requires PHP 7.1.0
     */
    public function testNamespaceExtractor71()
    {
        $parser = new NameSpaceExtractor(self::$RINDOW_TEST_RESOURCES.'/annotation/namespace/namespace71.php');
        $imports = $parser->getAllImports();
        $this->assertCount(1,$imports);
        $this->assertCount(2,$imports[__NAMESPACE__.'\Foo71']);
        $this->assertEquals('Rindow\Stdlib\PriorityQueue', $imports[__NAMESPACE__.'\Foo71']['PriorityQueue']);
        $this->assertEquals('Rindow\Stdlib\ListCollection',$imports[__NAMESPACE__.'\Foo71']['ListCollection1']);
        $classes = $parser->getAllClass();
        $this->assertNull($classes);
    }

    public function testNamespaceExtractorWithoutTrait()
    {
        $parser = new NameSpaceExtractor(self::$RINDOW_TEST_RESOURCES.'/annotation/namespace/namespace_without_trait.php');
        $imports = $parser->getAllImports();
        $this->assertEquals(3,count($imports));
        $this->assertEquals(1,count($imports[__NAMESPACE__.'\FooWithoutTrait']));
        $this->assertEquals(1,count($imports[__NAMESPACE__.'\BarWithoutTrait']));
        $this->assertEquals(2,count($imports['__TOPLEVEL__']));
        $this->assertEquals('Rindow\Stdlib\ListCollection',$imports[__NAMESPACE__.'\FooWithoutTrait']['ListCollection']);
        $this->assertEquals('Rindow\Stdlib\PriorityQueue',$imports[__NAMESPACE__.'\BarWithoutTrait']['ListCollection']);
        $this->assertEquals('stdClass',$imports['__TOPLEVEL__']['ListCollection']);
        $this->assertEquals('Rindow\TestList',$imports['__TOPLEVEL__']['TestList']);
        $classes = $parser->getAllClass();
        $this->assertEquals(__NAMESPACE__.'\FooWithoutTrait\MyClass',$classes[0]);
        $this->assertEquals(__NAMESPACE__.'\FooWithoutTrait\MyClass2',$classes[1]);
        $this->assertEquals(__NAMESPACE__.'\BarWithoutTrait\MyClass',$classes[2]);
    }

    /**
     * @requires PHP 5.4.0
     */
    public function testNamespaceExtractorWithoutNamespace()
    {
        $parser = new NameSpaceExtractor(self::$RINDOW_TEST_RESOURCES.'/annotation/namespace/namespace_without_namespace.php');
        $imports = $parser->getAllImports();
        $this->assertEquals(1,count($imports));
        $this->assertEquals(4,count($imports['__TOPLEVEL__']));
        $this->assertEquals('Rindow\Stdlib\ListCollection',$imports['__TOPLEVEL__']['ListCollection']);
        $this->assertEquals('Rindow\Stdlib\PriorityQueue',$imports['__TOPLEVEL__']['ListCollection2']);
        $this->assertEquals('stdClass',$imports['__TOPLEVEL__']['ListCollection3']);
        $this->assertEquals('Rindow\TestList',$imports['__TOPLEVEL__']['TestList']);

        $imports = $parser->getImports('');
        $this->assertEquals(4,count($imports));
        $this->assertEquals('Rindow\Stdlib\ListCollection',$imports['ListCollection']);
        $this->assertEquals('Rindow\Stdlib\PriorityQueue',$imports['ListCollection2']);
        $this->assertEquals('stdClass',$imports['ListCollection3']);
        $this->assertEquals('Rindow\TestList',$imports['TestList']);

        $classes = $parser->getAllClass();
        $this->assertEquals(str_replace('\\', '_', __NAMESPACE__.'\FooWithoutNamespace\MyClass') ,$classes[0]);
        $this->assertEquals(str_replace('\\', '_', __NAMESPACE__.'\FooWithoutNamespace\MyClass2'),$classes[1]);
        $this->assertEquals(str_replace('\\', '_', __NAMESPACE__.'\BarWithoutNamespace\MyClass') ,$classes[2]);

    }

    public function testImportsAbsolute()
    {
        $reader = new AnnotationManager();
        $ref = new ReflectionClass('RindowTest\Annotation\Entity\Entity1');
        $nameSpace = $ref->getNamespaceName();
        $className = $ref->name;
        $fileName  = $ref->getFileName();
        $location['class'] = $className;
        $reader->addImports($nameSpace,$className,$fileName);
        $this->assertEquals('RindowTest\Annotation\Mapping\Entity',$reader->resolvAnnotationClass('Entity',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\Table',$reader->resolvAnnotationClass('Table',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\Id',$reader->resolvAnnotationClass('Id',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\GeneratedValue',$reader->resolvAnnotationClass('GeneratedValue',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',$reader->resolvAnnotationClass('Column',$location));

        $annos = $reader->getClassAnnotations($ref);
        $this->assertEquals(2,count($annos));
        $this->assertEquals('RindowTest\Annotation\Mapping\Entity',get_class($annos[0]));
        $this->assertEquals('RindowTest\Annotation\Mapping\Table',get_class($annos[1]));

        $propertyRefs = $ref->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $fields[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(3,count($fields));
        $this->assertEquals(3,count($fields['id']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Id',get_class($fields['id'][0]));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',get_class($fields['id'][1]));
        $this->assertEquals('RindowTest\Annotation\Mapping\GeneratedValue',get_class($fields['id'][2]));
        $this->assertEquals(1,count($fields['name']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',get_class($fields['name'][0]));

        // import current namespace at @nest1
        $this->assertEquals(1,count($fields['nest']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Nest1',get_class($fields['nest'][0]));
        $metaData = $reader->getMetaData('RindowTest\Annotation\Mapping\Nest1');
        $this->assertEquals(3,count($metaData->classAnnotations));
        $this->assertEquals('Rindow\Annotation\Annotation\Annotation',get_class($metaData->classAnnotations[0]));
        $this->assertEquals('Rindow\Annotation\Annotation\Target',get_class($metaData->classAnnotations[1]));
        $this->assertEquals('RindowTest\Annotation\Mapping\Nest2',get_class($metaData->classAnnotations[2]));
    }

    public function testImportsNameSpace()
    {
        $reader = new AnnotationManager();
        $ref = new ReflectionClass('RindowTest\Annotation\Entity\Entity2');
        $nameSpace = $ref->getNamespaceName();
        $className = $ref->name;
        $fileName  = $ref->getFileName();
        $location['class'] = $className;
        $reader->addImports($nameSpace,$className,$fileName);
        $this->assertEquals('RindowTest\Annotation\Mapping\Entity',$reader->resolvAnnotationClass('Annotation\Mapping\Entity',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\Table',$reader->resolvAnnotationClass('Annotation\Mapping\Table',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\Id',$reader->resolvAnnotationClass('Annotation\Mapping\Id',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\GeneratedValue',$reader->resolvAnnotationClass('Annotation\Mapping\GeneratedValue',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',$reader->resolvAnnotationClass('Annotation\Mapping\Column',$location));

        $annos = $reader->getClassAnnotations($ref);
        $this->assertEquals(2,count($annos));
        $this->assertEquals('RindowTest\Annotation\Mapping\Entity',get_class($annos[0]));
        $this->assertEquals('RindowTest\Annotation\Mapping\Table',get_class($annos[1]));

        $propertyRefs = $ref->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $fields[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($fields));
        $this->assertEquals(3,count($fields['id']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Id',get_class($fields['id'][0]));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',get_class($fields['id'][1]));
        $this->assertEquals('RindowTest\Annotation\Mapping\GeneratedValue',get_class($fields['id'][2]));
        $this->assertEquals(1,count($fields['name']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',get_class($fields['name'][0]));
    }

    public function testImportsAlias()
    {
        $reader = new AnnotationManager();
        $ref = new ReflectionClass('RindowTest\Annotation\Entity\Entity3');
        $nameSpace = $ref->getNamespaceName();
        $className = $ref->name;
        $fileName  = $ref->getFileName();
        $location['class'] = $className;
        $reader->addImports($nameSpace,$className,$fileName);
        $this->assertEquals('RindowTest\Annotation\Mapping\Entity',$reader->resolvAnnotationClass('ORM\Entity',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\Table',$reader->resolvAnnotationClass('ORM\Table',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\Id',$reader->resolvAnnotationClass('ORM\Id',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\GeneratedValue',$reader->resolvAnnotationClass('ORM\GeneratedValue',$location));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',$reader->resolvAnnotationClass('ORM\Column',$location));

        $annos = $reader->getClassAnnotations($ref);
        $this->assertEquals(2,count($annos));
        $this->assertEquals('RindowTest\Annotation\Mapping\Entity',get_class($annos[0]));
        $this->assertEquals('RindowTest\Annotation\Mapping\Table',get_class($annos[1]));

        $propertyRefs = $ref->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $fields[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($fields));
        $this->assertEquals(3,count($fields['id']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Id',get_class($fields['id'][0]));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',get_class($fields['id'][1]));
        $this->assertEquals('RindowTest\Annotation\Mapping\GeneratedValue',get_class($fields['id'][2]));
        $this->assertEquals(1,count($fields['name']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Column',get_class($fields['name'][0]));
    }

    public function testSubClass()
    {
        $reader = new AnnotationManager();
        $ref = new ReflectionClass('RindowTest\Annotation\Entity\ServiceSubClass');
        $annos = $reader->getClassAnnotations($ref);
        $this->assertEquals(2,count($annos));
        $this->assertEquals('RindowTest\Annotation\Mapping\Entity',get_class($annos[0]));
        $this->assertEquals('RindowTest\Annotation\Mapping\Table',get_class($annos[1]));
        $this->assertEquals('subproducts',$annos[1]->name);

        $propertyRefs = $ref->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $fields[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($fields));
        $this->assertEquals(1,count($fields['id']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Id',get_class($fields['id'][0]));
        $this->assertEquals(0,count($fields['manager']));

        $methodRefs = $ref->getMethods();
        foreach($methodRefs as $methodRef) {
            $methods[$methodRef->getName()] = $reader->getMethodAnnotations($methodRef);
        }
        $this->assertEquals(1,count($methods));
        $this->assertEquals(1,count($methods['setManager']));
        $this->assertEquals('RindowTest\Annotation\Mapping\Inject',get_class($methods['setManager'][0]));
    }

}
