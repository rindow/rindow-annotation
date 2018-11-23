<?php
namespace Rindow\Annotation;

use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;
use Rindow\Stdlib\Cache\CacheHandlerTemplate;
use Rindow\Annotation\Annotation\Annotation as AnnotationTag;
use Rindow\Annotation\Annotation\Target as TargetTag;
use Rindow\Annotation\Annotation\Enum as EnumTag;
use Rindow\Annotation\Annotation\NotRegisterAnnotationInterface;
use Interop\Lenient\Annotation\AnnotationReader;

class AnnotationManager implements AnnotationReader
{
    private static $cachePath;
    protected $cacheHandler;
    protected $parser;
    protected $nameSpaces;
    protected $ignoreUnknownAnnotationMode;
    protected $events;
    protected $annotationProvider = array();
    protected $notRegisterAnnotationInterface;
    protected $aliases = array();
/*
    public static function factory()
    {
        return CacheHandlerTemplate::instanceFactory(__CLASS__,self::$cachePath);
    }
*/
    public function __construct()
    {
        $this->cacheHandler = new CacheHandlerTemplate(__CLASS__);
        $this->nameSpaces = array(__NAMESPACE__.'\\'.'Annotation'=>__NAMESPACE__.'\\'.'Annotation');
        $this->parser = new Parser($this);
        $this->notRegisterAnnotationInterface = __NAMESPACE__.'\NotRegisterAnnotationInterface';
    }

    public function getParser()
    {
        return $this->parser;
    }

    protected function getClassCache()
    {
        return $this->cacheHandler->getCache('classdata');
    }

    protected function getMethodCache()
    {
        return $this->cacheHandler->getCache('methoddata');
    }

    protected function getPropertyCache()
    {
        return $this->cacheHandler->getCache('propertydata');
    }

    protected function getCache()
    {
        return $this->cacheHandler->getCache('metadata');
    }

    protected function getImportsCache()
    {
        return $this->cacheHandler->getCache('imports');
    }

    public function setEnableCache($enableCache=true)
    {
        $this->cacheHandler->setEnableCache($enableCache);
    }

    public function setCachePath($cachePath)
    {
        $this->cacheHandler->setCachePath($cachePath);
    }

    public function addNameSpace($nameSpace)
    {
        $this->nameSpaces[$nameSpace] = $nameSpace;
        return $this;
    }

    public function addNameSpaces(array $nameSpaces)
    {
        foreach ($nameSpaces as $nameSpace) {
            $this->addNameSpace($nameSpace);
        }
        return $this;
    }

    public function AddAlias($alias,$className)
    {
        $this->aliases[$alias] = $className;
        return $this;
    }

    public function addAliases(array $aliases)
    {
        foreach ($aliases as $alias => $className) {
            $this->AddAlias($alias,$className);
        }
        return $this;
    }

    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
        return $this;
    }

    public function ignoreUnknownAnnotation($mode=true)
    {
        $this->ignoreUnknownAnnotationMode = $mode;
        return $this;
    }

    public function getAllMetaData($class)
    {
        if($class instanceof ReflectionClass)
            $ref = $class;
        else
            $ref = new ReflectionClass($class);
        if($ref->isInternal())
            return false;
        $nameSpace = $ref->getNamespaceName();
        $className = $ref->name;
        $fileName  = $ref->getFileName();
        $lineNumber = $ref->getStartLine();
        $this->addImports($nameSpace,$className,$fileName);
        return $this->registMetaData($ref);
    }
/*
    public function getClassAnnotations(ReflectionClass $ref)
    {
        if($ref->isInternal())
            return array();
        $index = $ref->name;
        $classCache = $this->getClassCache();
        if(isset($classCache[$index]))
            return $classCache[$index]->classAnnotations;
        $metaData = new AnnotationMetaData();
        $metaData->classAnnotations = array_values($this->createClassAnnotations($ref));
        $classCache[$index] = $metaData;
        return $metaData->classAnnotations;
    }
*/
    public function getClassAnnotations(ReflectionClass $ref)
    {
        if($ref->isInternal())
            return array();
        $index = $ref->name;
        $classCache = $this->getClassCache();
        $manager = $this;
        $metaData = $classCache->get(
            $index,
            false,
            function ($cache,$index,&$entry) use ($ref,$manager) {
                $entry = new AnnotationMetaData();
                $entry->classAnnotations = array_values($manager->createClassAnnotations($ref));
                return true;
            }
        );
        return $metaData->classAnnotations;
    }

    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        foreach ($this->getClassAnnotations($class) as $anno) {
            if ($anno instanceof $annotationName) {
                return $anno;
            }
        }
        return null;
    }
/*
    public function getMethodAnnotations(ReflectionMethod $ref)
    {
        if($ref->isInternal())
            return array();
        $classRef  = $ref->getDeclaringClass();
        $index = $classRef->name.'::'.$ref->name;
        $methodCache = $this->getMethodCache();
        if(isset($methodCache[$index]))
            return $methodCache[$index]->methodAnnotations;
        $metaData = new AnnotationMetaData();
        $metaData->methodAnnotations = array_values($this->createMethodAnnotations($ref));
        $methodCache[$index] = $metaData;
        return $metaData->methodAnnotations;
    }
*/
    public function getMethodAnnotations(ReflectionMethod $ref)
    {
        if($ref->isInternal())
            return array();
        $classRef  = $ref->getDeclaringClass();
        $index = $classRef->name.'::'.$ref->name;
        $methodCache = $this->getMethodCache();
        $manager = $this;
        $metaData = $methodCache->get(
            $index,
            false,
            function ($cache,$index,&$entry) use ($ref,$manager) {
                $entry = new AnnotationMetaData();
                $entry->methodAnnotations = array_values($manager->createMethodAnnotations($ref));
                return true;
            }
        );
        return $metaData->methodAnnotations;
    }

    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        foreach ($this->getMethodAnnotations($method) as $anno) {
            if ($anno instanceof $annotationName) {
                return $anno;
            }
        }
        return null;
    }
/*
    public function getPropertyAnnotations(ReflectionProperty $ref)
    {
        $classRef  = $ref->getDeclaringClass();
        if($classRef->isInternal())
            return array();
        $index = $classRef->name.'::$'.$ref->name;
        $propertyCache = $this->getPropertyCache();
        if(isset($propertyCache[$index]))
            return $propertyCache[$index]->fieldAnnotations;
        $metaData = new AnnotationMetaData();
        $metaData->fieldAnnotations = array_values($this->createPropertyAnnotations($ref));
        $propertyCache[$index] = $metaData;
        return $metaData->fieldAnnotations;
    }
*/
    public function getPropertyAnnotations(ReflectionProperty $ref)
    {
        $classRef  = $ref->getDeclaringClass();
        if($classRef->isInternal())
            return array();
        $index = $classRef->name.'::$'.$ref->name;
        $propertyCache = $this->getPropertyCache();
        $manager = $this;
        $metaData = $propertyCache->get(
            $index,
            false,
            function ($cache,$index,&$entry) use ($ref,$manager) {
                $entry = new AnnotationMetaData();
                $entry->fieldAnnotations = array_values($manager->createPropertyAnnotations($ref));
                return true;
            }
        );
        return $metaData->fieldAnnotations;
    }

    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        foreach ($this->getPropertyAnnotations($property) as $anno) {
            if ($anno instanceof $annotationName) {
                return $anno;
            }
        }
        return null;
    }

    protected function registAnnotationProvider($annotationClassName)
    {
        if(array_key_exists($annotationClassName, $this->annotationProvider))
            return $this->annotationProvider[$annotationClassName];

        $providerName = $annotationClassName.'Provider';
        if(!class_exists($providerName)) {
            $this->annotationProvider[$annotationClassName] = false;
            return false;
        }
        $provider = new $providerName();
        if(!$provider instanceof AnnotationProviderInterface) {
            $this->annotationProvider[$annotationClassName] = false;
            return false;
        }

        $this->annotationProvider[$annotationClassName] = $provider;
        foreach ($provider->getJoinPoints() as $method => $eventNames ) {
            foreach($eventNames as $eventName) {
                $this->events[$annotationClassName.'::'.$eventName][] = array($provider,$method);
            }
        }
        return $provider;
    }

    protected function executeAnnotationProvider($eventName,$annotationClassName,array $args)
    {
        $this->registAnnotationProvider($annotationClassName);
        $name = $annotationClassName.'::'.$eventName;
        if(!isset($this->events[$name]))
            return;
        foreach ($this->events[$name] as $listener) {
            $event = new Event($name,$args);
            call_user_func($listener,$event);
        }
    }

    public function createAnnotation($annotationName,$args,$location)
    {
        $className = $this->resolvAnnotationClass($annotationName,$location);
        if($className===false) {
            if($this->ignoreUnknownAnnotationMode)
                return false;
            throw new Exception\DomainException('a class is not found for the annotation:@'.$annotationName.' in '.$location['uri'].': '.$location['filename'].'('.$location['linenumber'].')');
        }

        $refClass = new ReflectionClass($className);
        if($refClass->isInternal()) {
            return false;
        }

        $metaData = $this->registMetaData($refClass,ElementType::ANNOTATION_TYPE);
        if($metaData) {
            $isAnnotation = false;
            foreach ($metaData->classAnnotations as $anno) {
                if($anno instanceof AnnotationTag) {
                    $isAnnotation = true;
                    break;
                }
            }
            if(!$isAnnotation) {
                throw new Exception\DomainException("the class is not annotation class.: ".$className.' in '.$location['uri'].': '.$location['filename'].'('.$location['linenumber'].')');
            }

            $this->executeClassAnnotationProvider($className,$metaData,$location);
        }

        if($args!==null) {
            if(is_array($args) && array_key_exists(0, $args)) {
                $value = $args;
                $args = array();
                $args['value'] = $value;
            } else if(!is_array($args)) {
                $value = $args;
                $args = array();
                $args['value'] = $value;
            }
            foreach($args as $field => $value) {
                $this->executeFieldAnnotationProvider($className,$field,$value,$metaData,$location);
            }
        }

        if(isset($metaData->hasConstructor) && $metaData->hasConstructor) {
            // Compatibility For Doctrine Annotation Reader
            $annotation = new $className($args);
        } else {
            // General Annotation
            $annotation = new $className();
            if($args!==null) {
                foreach($args as $field => $value) {
                    if(!property_exists($annotation, $field))
                        throw new Exception\DomainException('the argument "'.$field.'" is invalid for @'.$className.': in '.$location['uri'].': '.$location['filename'].'('.$location['linenumber'].')');
                    $annotation->{$field} = $value;
                }
            }
        }

        $this->initalizeAnnotationMetaData(
            $className,
            $annotation,
            $location
        );
        return $annotation;
    }

    public function addImports($nameSpace,$className,$fileName)
    {
        $importsCache = $this->getImportsCache();
        if(isset($importsCache[$className])) {
            return $this;
        }
        $nameSpaceExtractor = new NameSpaceExtractor($fileName);
        $importsCache[$className] = $nameSpaceExtractor->getImports($nameSpace);
        return $this;
    }

    protected function resolvAlias($alias)
    {
        if(isset($this->aliases[$alias]))
            return $this->aliases[$alias];
        return $alias;
    }

    public function resolvAnnotationClass($annotationName,$location)
    {
        if(substr($annotationName, 0, 1)=='\\') {
            $annotationName = $this->resolvAlias($annotationName);
            if(class_exists($annotationName))
                return $annotationName;
            return false;
        }
        $importsCache = $this->getImportsCache();
        $class = $location['class'];
        if(isset($importsCache[$class])) {
            $imports = $importsCache[$class];
            $pieces = explode('\\',$annotationName);
            $alias = array_shift($pieces);
            if(isset($imports[$alias])) {
                $className = $imports[$alias];
                if(count($pieces))
                    $className .= '\\' . implode('\\', $pieces);
                $className = $this->resolvAlias($className);
                if(class_exists($className)) {
                    return $className;
                }
            }
        }
        $pieces = explode('\\',$class);
        $className = $annotationName;
        array_pop($pieces);
        if(count($pieces))
            $className = implode('\\', $pieces) . '\\' . $className;
        $className = $this->resolvAlias($className);
        if(class_exists($className))
            return $className;

        foreach($this->nameSpaces as $namespace) {
            $className = $namespace.'\\'.$annotationName;
            $className = $this->resolvAlias($className);
            if(class_exists($className)) {
                return $className;
            }
        }
        return false;
    }

    protected function executeClassAnnotationProvider($className,$metaData,$location)
    {
        if(!isset($metaData->classAnnotations))
            return;
        foreach($metaData->classAnnotations as $classAnnotation) {
            $this->executeAnnotationProvider(
                AnnotationProviderInterface::EVENT_USED_PARENT,
                get_class($classAnnotation),
                array(
                    'annotationname'=> $className,
                    'metadata'  => $classAnnotation,
                    'location'  => $location,
                )
            );
        }
    }

    protected function executeFieldAnnotationProvider($className,$field,$value,$metaData,$location)
    {
        if(!isset($metaData->fieldAnnotations[$field]))
            return;
        foreach($metaData->fieldAnnotations[$field] as $fieldAnnotation) {
            $this->executeAnnotationProvider(
                AnnotationProviderInterface::EVENT_SET_FIELD,
                get_class($fieldAnnotation),
                array(
                    'annotationname'=> $className,
                    'fieldname' => $field,
                    'value'    => $value,
                    'metadata' => $fieldAnnotation,
                    'location' => $location
                )
            );
        }
    }

    protected function initalizeAnnotationMetaData($className,$annotation,$location)
    {
        $this->executeAnnotationProvider(
            AnnotationProviderInterface::EVENT_CREATED,
            get_class($annotation),
            array(
                'annotationname' => $className,
                'metadata' => $annotation,
                'location' => $location,
            )
        );
    }
/*
    public function getMetaData($annotationClassName)
    {
        if(is_object($annotationClassName))
            $annotationClassName = get_class($annotationClassName);
        else if(!is_string($annotationClassName))
            throw new Exception\DomainException("the annotation must be a object or a class name.", 1);
            
        $metaDataCache = $this->getCache();
        if(!isset($metaDataCache[$annotationClassName]))
            return false;
        return $metaDataCache[$annotationClassName];
    }
*/
    public function getMetaData($annotationClassName)
    {
        if(is_object($annotationClassName))
            $annotationClassName = get_class($annotationClassName);
        else if(!is_string($annotationClassName))
            throw new Exception\DomainException("the annotation must be a object or a class name.", 1);
            
        $metaDataCache = $this->getCache();
        return $metaDataCache->get($annotationClassName,false);
    }
/*
    protected function registMetaData(ReflectionClass $classRef,$type=ElementType::TYPE)
    {
        $annotationClassName = $classRef->name;
        $metaDataCache = $this->getCache();
        if(isset($metaDataCache[$annotationClassName])) {
            return $metaDataCache[$annotationClassName];
        }
        $metaData = $this->createMetaData($classRef,$type);
        if($metaData==false)
            return false;
        $metaDataCache[$annotationClassName] = $metaData;
        return $metaData;
    }
*/
    protected function registMetaData(ReflectionClass $classRef,$type=ElementType::TYPE)
    {
        $annotationClassName = $classRef->name;
        $metaDataCache = $this->getCache();
        $manager = $this;
        $metaData = $metaDataCache->get(
            $annotationClassName,
            false,
            function ($cache,$index,&$entry) use ($classRef,$type,$manager) {
                $metaData = $manager->createMetaData($classRef,$type);
                if($metaData==false) {
                    return false;
                }
                $entry = $metaData;
                return true;
            }
        );
        return $metaData;
    }

    public function createMetaData(ReflectionClass $classRef,$elementType)
    {
        if($classRef->implementsInterface($this->notRegisterAnnotationInterface))
            return false;

        $nameSpace = $classRef->getNamespaceName();
        $fileName  = $classRef->getFileName();
        $lineNumber = $classRef->getStartLine();
        $this->addImports($nameSpace,$classRef->name,$fileName);

        $metaData = new AnnotationMetaData();
        $metaData->className = $classRef->name;
        $metaData->hasConstructor = ($classRef->getConstructor()) ? true : false;
        $metaDataFileName  = $classRef->getFileName();
        $metaData->classAnnotations = array_values($this->createClassAnnotations($classRef,$elementType));

        $propRefs = $classRef->getProperties();
        foreach ($propRefs as $propRef) {
            $annos = $this->createPropertyAnnotations($propRef);
            foreach($annos as $anno) {
                $fieldName = $propRef->getName();
                if(!isset($metaData->fieldAnnotations[$fieldName]))
                    $metaData->fieldAnnotations[$fieldName] = array();
                $metaData->fieldAnnotations[$fieldName][] = $anno;
            }
        }
        $methodRefs = $classRef->getMethods();
        foreach ($methodRefs as $methodRef) {
            $annos = $this->createMethodAnnotations($methodRef);
            foreach($annos as $anno) {
                $methodName = $methodRef->getName();
                if(!isset($metaData->methodAnnotations[$methodName]))
                    $metaData->methodAnnotations[$methodName] = array();
                $metaData->methodAnnotations[$methodName][] = $anno;
            }
        }
        return $metaData;
    }

    public function createClassAnnotations(ReflectionClass $ref, $elementType=ElementType::TYPE)
    {
        if($ref->isInternal())
            return array();
        $parentRef = $ref->getParentClass();
        $parentAnnos = array();
        if($parentRef) {
            $parentAnnos = $this->createClassAnnotations($parentRef, $elementType);
        }
        $location['target'] = $elementType;
        $location['class']  = $ref->name;
        $location['name']   = $ref->name;
        $location['uri']    = $ref->name;
        $location['filename']   = $ref->getFileName();
        $location['linenumber'] = $ref->getStartLine();
        $this->addImports(
            $ref->getNamespaceName(),
            $location['class'],
            $location['filename']);
        $annos = $this->parser->searchAnnotation($ref->getDocComment(),$location);
        return array_merge($parentAnnos, $annos);
    }

    public function createPropertyAnnotations(ReflectionProperty $ref)
    {
        $classRef  = $ref->getDeclaringClass();
        if($classRef->isInternal()) {
            return array();
        }
        $location['target'] = ElementType::FIELD;
        $location['class']  = $classRef->name;
        $location['name']   = $ref->name;
        $location['uri']    = $classRef->name.'::$'.$ref->name;
        $location['filename']   = $classRef->getFileName();
        $location['linenumber'] = $classRef->getStartLine();
        $this->addImports(
            $classRef->getNamespaceName(),
            $location['class'],
            $location['filename']);
        return $this->parser->searchAnnotation($ref->getDocComment(),$location);
    }

    public function createMethodAnnotations(ReflectionMethod $ref)
    {
        if($ref->isInternal()) {
            return array();
        }
        $classRef  = $ref->getDeclaringClass();
        $location['target'] = ElementType::METHOD;
        $location['class']  = $classRef->name;
        $location['name']   = $ref->name;
        $location['uri']    = $classRef->name.'::'.$ref->name.'()';
        $location['filename']   = $classRef->getFileName();
        $location['linenumber'] = $classRef->getStartLine();
        $this->addImports(
            $classRef->getNamespaceName(),
            $location['class'],
            $location['filename']);
        return $this->parser->searchAnnotation($ref->getDocComment(),$location);
    }
}