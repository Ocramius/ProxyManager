<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class to play around with mixed visibility properties with different type definitions
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithMixedReferenceableTypedProperties
{
    public static $publicStaticUnTypedProperty =  'publicStaticUnTypedProperty';
    public static $publicStaticUnTypedPropertyWithoutDefaultValue;
    public static bool $publicStaticBoolProperty =  true;
    public static bool $publicStaticBoolPropertyWithoutDefaultValue;
    public static ?bool $publicStaticNullableBoolProperty =  true;
    public static ?bool $publicStaticNullableBoolPropertyWithoutDefaultValue;
    public static int $publicStaticIntProperty =  123;
    public static int $publicStaticIntPropertyWithoutDefaultValue;
    public static ?int $publicStaticNullableIntProperty =  123;
    public static ?int $publicStaticNullableIntPropertyWithoutDefaultValue;
    public static float $publicStaticFloatProperty =  123.456;
    public static float $publicStaticFloatPropertyWithoutDefaultValue;
    public static ?float $publicStaticNullableFloatProperty =  123.456;
    public static ?float $publicStaticNullableFloatPropertyWithoutDefaultValue;
    public static string $publicStaticStringProperty =  'publicStaticStringProperty';
    public static string $publicStaticStringPropertyWithoutDefaultValue;
    public static ?string $publicStaticNullableStringProperty =  'publicStaticStringProperty';
    public static ?string $publicStaticNullableStringPropertyWithoutDefaultValue;
    public static array $publicStaticArrayProperty =  ['publicStaticArrayProperty'];
    public static array $publicStaticArrayPropertyWithoutDefaultValue;
    public static ?array $publicStaticNullableArrayProperty =  ['publicStaticArrayProperty'];
    public static ?array $publicStaticNullableArrayPropertyWithoutDefaultValue;
    public static iterable $publicStaticIterableProperty =  ['publicStaticIterableProperty'];
    public static iterable $publicStaticIterablePropertyWithoutDefaultValue;
    public static ?iterable $publicStaticNullableIterableProperty =  ['publicStaticIterableProperty'];
    public static ?iterable $publicStaticNullableIterablePropertyWithoutDefaultValue;
    public static object $publicStaticObjectProperty;
    public static ?object $publicStaticNullableObjectProperty;
    public static EmptyClass $publicStaticClassProperty;
    public static ?EmptyClass $publicStaticNullableClassProperty;

    protected static $protectedStaticUnTypedProperty =  'protectedStaticUnTypedProperty';
    protected static $protectedStaticUnTypedPropertyWithoutDefaultValue;
    protected static bool $protectedStaticBoolProperty =  true;
    protected static bool $protectedStaticBoolPropertyWithoutDefaultValue;
    protected static ?bool $protectedStaticNullableBoolProperty =  true;
    protected static ?bool $protectedStaticNullableBoolPropertyWithoutDefaultValue;
    protected static int $protectedStaticIntProperty =  123;
    protected static int $protectedStaticIntPropertyWithoutDefaultValue;
    protected static ?int $protectedStaticNullableIntProperty =  123;
    protected static ?int $protectedStaticNullableIntPropertyWithoutDefaultValue;
    protected static float $protectedStaticFloatProperty =  123.456;
    protected static float $protectedStaticFloatPropertyWithoutDefaultValue;
    protected static ?float $protectedStaticNullableFloatProperty =  123.456;
    protected static ?float $protectedStaticNullableFloatPropertyWithoutDefaultValue;
    protected static string $protectedStaticStringProperty =  'protectedStaticStringProperty';
    protected static string $protectedStaticStringPropertyWithoutDefaultValue;
    protected static ?string $protectedStaticNullableStringProperty =  'protectedStaticStringProperty';
    protected static ?string $protectedStaticNullableStringPropertyWithoutDefaultValue;
    protected static array $protectedStaticArrayProperty =  ['protectedStaticArrayProperty'];
    protected static array $protectedStaticArrayPropertyWithoutDefaultValue;
    protected static ?array $protectedStaticNullableArrayProperty =  ['protectedStaticArrayProperty'];
    protected static ?array $protectedStaticNullableArrayPropertyWithoutDefaultValue;
    protected static iterable $protectedStaticIterableProperty =  ['protectedStaticIterableProperty'];
    protected static iterable $protectedStaticIterablePropertyWithoutDefaultValue;
    protected static ?iterable $protectedStaticNullableIterableProperty =  ['protectedStaticIterableProperty'];
    protected static ?iterable $protectedStaticNullableIterablePropertyWithoutDefaultValue;
    protected static object $protectedStaticObjectProperty;
    protected static ?object $protectedStaticNullableObjectProperty;
    protected static EmptyClass $protectedStaticClassProperty;
    protected static ?EmptyClass $protectedStaticNullableClassProperty;

    private static $privateStaticUnTypedProperty =  'privateStaticUnTypedProperty';
    private static $privateStaticUnTypedPropertyWithoutDefaultValue;
    private static bool $privateStaticBoolProperty =  true;
    private static bool $privateStaticBoolPropertyWithoutDefaultValue;
    private static ?bool $privateStaticNullableBoolProperty =  true;
    private static ?bool $privateStaticNullableBoolPropertyWithoutDefaultValue;
    private static int $privateStaticIntProperty =  123;
    private static int $privateStaticIntPropertyWithoutDefaultValue;
    private static ?int $privateStaticNullableIntProperty =  123;
    private static ?int $privateStaticNullableIntPropertyWithoutDefaultValue;
    private static float $privateStaticFloatProperty =  123.456;
    private static float $privateStaticFloatPropertyWithoutDefaultValue;
    private static ?float $privateStaticNullableFloatProperty =  123.456;
    private static ?float $privateStaticNullableFloatPropertyWithoutDefaultValue;
    private static string $privateStaticStringProperty =  'privateStaticStringProperty';
    private static string $privateStaticStringPropertyWithoutDefaultValue;
    private static ?string $privateStaticNullableStringProperty =  'privateStaticStringProperty';
    private static ?string $privateStaticNullableStringPropertyWithoutDefaultValue;
    private static array $privateStaticArrayProperty =  ['privateStaticArrayProperty'];
    private static array $privateStaticArrayPropertyWithoutDefaultValue;
    private static ?array $privateStaticNullableArrayProperty =  ['privateStaticArrayProperty'];
    private static ?array $privateStaticNullableArrayPropertyWithoutDefaultValue;
    private static iterable $privateStaticIterableProperty =  ['privateStaticIterableProperty'];
    private static iterable $privateStaticIterablePropertyWithoutDefaultValue;
    private static ?iterable $privateStaticNullableIterableProperty =  ['privateStaticIterableProperty'];
    private static ?iterable $privateStaticNullableIterablePropertyWithoutDefaultValue;
    private static object $privateStaticObjectProperty;
    private static ?object $privateStaticNullableObjectProperty;
    private static EmptyClass $privateStaticClassProperty;
    private static ?EmptyClass $privateStaticNullableClassProperty;

    public $publicUnTypedProperty =  'publicUnTypedProperty';
    public bool $publicBoolProperty =  true;
    public ?bool $publicNullableBoolProperty =  true;
    public int $publicIntProperty =  123;
    public ?int $publicNullableIntProperty =  123;
    public float $publicFloatProperty =  123.456;
    public ?float $publicNullableFloatProperty =  123.456;
    public string $publicStringProperty =  'publicStringProperty';
    public ?string $publicNullableStringProperty =  'publicStringProperty';
    public array $publicArrayProperty =  ['publicArrayProperty'];
    public ?array $publicNullableArrayProperty =  ['publicArrayProperty'];
    public iterable $publicIterableProperty =  ['publicIterableProperty'];
    public ?iterable $publicNullableIterableProperty =  ['publicIterableProperty'];

    protected $protectedUnTypedProperty =  'protectedUnTypedProperty';
    protected bool $protectedBoolProperty =  true;
    protected ?bool $protectedNullableBoolProperty =  true;
    protected int $protectedIntProperty =  123;
    protected ?int $protectedNullableIntProperty =  123;
    protected float $protectedFloatProperty =  123.456;
    protected ?float $protectedNullableFloatProperty =  123.456;
    protected string $protectedStringProperty =  'protectedStringProperty';
    protected ?string $protectedNullableStringProperty =  'protectedStringProperty';
    protected array $protectedArrayProperty =  ['protectedArrayProperty'];
    protected ?array $protectedNullableArrayProperty =  ['protectedArrayProperty'];
    protected iterable $protectedIterableProperty =  ['protectedIterableProperty'];
    protected ?iterable $protectedNullableIterableProperty =  ['protectedIterableProperty'];

    private $privateUnTypedProperty =  'privateUnTypedProperty';
    private bool $privateBoolProperty =  true;
    private ?bool $privateNullableBoolProperty =  true;
    private int $privateIntProperty =  123;
    private ?int $privateNullableIntProperty =  123;
    private float $privateFloatProperty =  123.456;
    private ?float $privateNullableFloatProperty =  123.456;
    private string $privateStringProperty =  'privateStringProperty';
    private ?string $privateNullableStringProperty =  'privateStringProperty';
    private array $privateArrayProperty =  ['privateArrayProperty'];
    private ?array $privateNullableArrayProperty =  ['privateArrayProperty'];
    private iterable $privateIterableProperty =  ['privateIterableProperty'];
    private ?iterable $privateNullableIterableProperty =  ['privateIterableProperty'];
}
