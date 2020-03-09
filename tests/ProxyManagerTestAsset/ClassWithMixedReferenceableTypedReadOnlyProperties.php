<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class to play around with final typed properties with different type definitions
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithMixedReferenceableTypedReadOnlyProperties
{
    final public static $publicStaticUnTypedPropertyWithoutDefaultValue;
    final public static bool $publicStaticBoolProperty =  true;
    final public static bool $publicStaticBoolPropertyWithoutDefaultValue;
    final public static ?bool $publicStaticNullableBoolProperty =  true;
    final public static ?bool $publicStaticNullableBoolPropertyWithoutDefaultValue;
    final public static int $publicStaticIntProperty =  123;
    final public static int $publicStaticIntPropertyWithoutDefaultValue;
    final public static ?int $publicStaticNullableIntProperty =  123;
    final public static ?int $publicStaticNullableIntPropertyWithoutDefaultValue;
    final public static float $publicStaticFloatProperty =  123.456;
    final public static float $publicStaticFloatPropertyWithoutDefaultValue;
    final public static ?float $publicStaticNullableFloatProperty =  123.456;
    final public static ?float $publicStaticNullableFloatPropertyWithoutDefaultValue;
    final public static string $publicStaticStringProperty =  'publicStaticStringProperty';
    final public static string $publicStaticStringPropertyWithoutDefaultValue;
    final public static ?string $publicStaticNullableStringProperty =  'publicStaticStringProperty';
    final public static ?string $publicStaticNullableStringPropertyWithoutDefaultValue;
    final public static array $publicStaticArrayProperty =  ['publicStaticArrayProperty'];
    final public static array $publicStaticArrayPropertyWithoutDefaultValue;
    final public static ?array $publicStaticNullableArrayProperty =  ['publicStaticArrayProperty'];
    final public static ?array $publicStaticNullableArrayPropertyWithoutDefaultValue;
    final public static iterable $publicStaticIterableProperty =  ['publicStaticIterableProperty'];
    final public static iterable $publicStaticIterablePropertyWithoutDefaultValue;
    final public static ?iterable $publicStaticNullableIterableProperty =  ['publicStaticIterableProperty'];
    final public static ?iterable $publicStaticNullableIterablePropertyWithoutDefaultValue;
    final public static object $publicStaticObjectProperty;
    final public static ?object $publicStaticNullableObjectProperty;
    final public static EmptyClass $publicStaticClassProperty;
    final public static ?EmptyClass $publicStaticNullableClassProperty;

    final protected static $protectedStaticUnTypedProperty =  'protectedStaticUnTypedProperty';
    final protected static $protectedStaticUnTypedPropertyWithoutDefaultValue;
    final protected static bool $protectedStaticBoolProperty =  true;
    final protected static bool $protectedStaticBoolPropertyWithoutDefaultValue;
    final protected static ?bool $protectedStaticNullableBoolProperty =  true;
    final protected static ?bool $protectedStaticNullableBoolPropertyWithoutDefaultValue;
    final protected static int $protectedStaticIntProperty =  123;
    final protected static int $protectedStaticIntPropertyWithoutDefaultValue;
    final protected static ?int $protectedStaticNullableIntProperty =  123;
    final protected static ?int $protectedStaticNullableIntPropertyWithoutDefaultValue;
    final protected static float $protectedStaticFloatProperty =  123.456;
    final protected static float $protectedStaticFloatPropertyWithoutDefaultValue;
    final protected static ?float $protectedStaticNullableFloatProperty =  123.456;
    final protected static ?float $protectedStaticNullableFloatPropertyWithoutDefaultValue;
    final protected static string $protectedStaticStringProperty =  'protectedStaticStringProperty';
    final protected static string $protectedStaticStringPropertyWithoutDefaultValue;
    final protected static ?string $protectedStaticNullableStringProperty =  'protectedStaticStringProperty';
    final protected static ?string $protectedStaticNullableStringPropertyWithoutDefaultValue;
    final protected static array $protectedStaticArrayProperty =  ['protectedStaticArrayProperty'];
    final protected static array $protectedStaticArrayPropertyWithoutDefaultValue;
    final protected static ?array $protectedStaticNullableArrayProperty =  ['protectedStaticArrayProperty'];
    final protected static ?array $protectedStaticNullableArrayPropertyWithoutDefaultValue;
    final protected static iterable $protectedStaticIterableProperty =  ['protectedStaticIterableProperty'];
    final protected static iterable $protectedStaticIterablePropertyWithoutDefaultValue;
    final protected static ?iterable $protectedStaticNullableIterableProperty =  ['protectedStaticIterableProperty'];
    final protected static ?iterable $protectedStaticNullableIterablePropertyWithoutDefaultValue;
    final protected static object $protectedStaticObjectProperty;
    final protected static ?object $protectedStaticNullableObjectProperty;
    final protected static EmptyClass $protectedStaticClassProperty;
    final protected static ?EmptyClass $protectedStaticNullableClassProperty;

    final private static $privateStaticUnTypedProperty =  'privateStaticUnTypedProperty';
    final private static $privateStaticUnTypedPropertyWithoutDefaultValue;
    final private static bool $privateStaticBoolProperty =  true;
    final private static bool $privateStaticBoolPropertyWithoutDefaultValue;
    final private static ?bool $privateStaticNullableBoolProperty =  true;
    final private static ?bool $privateStaticNullableBoolPropertyWithoutDefaultValue;
    final private static int $privateStaticIntProperty =  123;
    final private static int $privateStaticIntPropertyWithoutDefaultValue;
    final private static ?int $privateStaticNullableIntProperty =  123;
    final private static ?int $privateStaticNullableIntPropertyWithoutDefaultValue;
    final private static float $privateStaticFloatProperty =  123.456;
    final private static float $privateStaticFloatPropertyWithoutDefaultValue;
    final private static ?float $privateStaticNullableFloatProperty =  123.456;
    final private static ?float $privateStaticNullableFloatPropertyWithoutDefaultValue;
    final private static string $privateStaticStringProperty =  'privateStaticStringProperty';
    final private static string $privateStaticStringPropertyWithoutDefaultValue;
    final private static ?string $privateStaticNullableStringProperty =  'privateStaticStringProperty';
    final private static ?string $privateStaticNullableStringPropertyWithoutDefaultValue;
    final private static array $privateStaticArrayProperty =  ['privateStaticArrayProperty'];
    final private static array $privateStaticArrayPropertyWithoutDefaultValue;
    final private static ?array $privateStaticNullableArrayProperty =  ['privateStaticArrayProperty'];
    final private static ?array $privateStaticNullableArrayPropertyWithoutDefaultValue;
    final private static iterable $privateStaticIterableProperty =  ['privateStaticIterableProperty'];
    final private static iterable $privateStaticIterablePropertyWithoutDefaultValue;
    final private static ?iterable $privateStaticNullableIterableProperty =  ['privateStaticIterableProperty'];
    final private static ?iterable $privateStaticNullableIterablePropertyWithoutDefaultValue;
    final private static object $privateStaticObjectProperty;
    final private static ?object $privateStaticNullableObjectProperty;
    final private static EmptyClass $privateStaticClassProperty;
    final private static ?EmptyClass $privateStaticNullableClassProperty;

    final public $publicUnTypedProperty =  'publicUnTypedProperty';
    final public bool $publicBoolProperty =  true;
    final public ?bool $publicNullableBoolProperty =  true;
    final public int $publicIntProperty =  123;
    final public ?int $publicNullableIntProperty =  123;
    final public float $publicFloatProperty =  123.456;
    final public ?float $publicNullableFloatProperty =  123.456;
    final public string $publicStringProperty =  'publicStringProperty';
    final public ?string $publicNullableStringProperty =  'publicStringProperty';
    final public array $publicArrayProperty =  ['publicArrayProperty'];
    final public ?array $publicNullableArrayProperty =  ['publicArrayProperty'];
    final public iterable $publicIterableProperty =  ['publicIterableProperty'];
    final public ?iterable $publicNullableIterableProperty =  ['publicIterableProperty'];

    final protected $protectedUnTypedProperty =  'protectedUnTypedProperty';
    final protected bool $protectedBoolProperty =  true;
    final protected ?bool $protectedNullableBoolProperty =  true;
    final protected int $protectedIntProperty =  123;
    final protected ?int $protectedNullableIntProperty =  123;
    final protected float $protectedFloatProperty =  123.456;
    final protected ?float $protectedNullableFloatProperty =  123.456;
    final protected string $protectedStringProperty =  'protectedStringProperty';
    final protected ?string $protectedNullableStringProperty =  'protectedStringProperty';
    final protected array $protectedArrayProperty =  ['protectedArrayProperty'];
    final protected ?array $protectedNullableArrayProperty =  ['protectedArrayProperty'];
    final protected iterable $protectedIterableProperty =  ['protectedIterableProperty'];
    final protected ?iterable $protectedNullableIterableProperty =  ['protectedIterableProperty'];

    final private $privateUnTypedProperty =  'privateUnTypedProperty';
    final private bool $privateBoolProperty =  true;
    final private ?bool $privateNullableBoolProperty =  true;
    final private int $privateIntProperty =  123;
    final private ?int $privateNullableIntProperty =  123;
    final private float $privateFloatProperty =  123.456;
    final private ?float $privateNullableFloatProperty =  123.456;
    final private string $privateStringProperty =  'privateStringProperty';
    final private ?string $privateNullableStringProperty =  'privateStringProperty';
    final private array $privateArrayProperty =  ['privateArrayProperty'];
    final private ?array $privateNullableArrayProperty =  ['privateArrayProperty'];
    final private iterable $privateIterableProperty =  ['privateIterableProperty'];
    final private ?iterable $privateNullableIterableProperty =  ['privateIterableProperty'];
}
