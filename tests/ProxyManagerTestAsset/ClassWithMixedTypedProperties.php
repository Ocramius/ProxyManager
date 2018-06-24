<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class to play around with mixed visibility properties with different type definitions
 *
 * @link @TODO link the RFC here
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithMixedTypedProperties
{
    public static $publicStaticUnTypedProperty               = 'publicStaticUnTypedProperty';
    public static bool $publicStaticBoolProperty               = true;
    public static ?bool $publicStaticNullableBoolProperty       = true;
    public static int $publicStaticIntProperty                = 123;
    public static ?int $publicStaticNullableIntProperty        = 123;
    public static float $publicStaticFloatProperty              = 123.456;
    public static ?float $publicStaticNullableFloatProperty      = 123.456;
    public static string $publicStaticStringProperty             = 'publicStaticStringProperty';
    public static ?string $publicStaticNullableStringProperty     = 'publicStaticStringProperty';
    public static array $publicStaticArrayProperty              = ['publicStaticArrayProperty'];
    public static ?array $publicStaticNullableArrayProperty      = ['publicStaticArrayProperty'];
    public static iterable $publicStaticIterableProperty           = ['publicStaticIterableProperty'];
    public static ?iterable $publicStaticNullableIterableProperty   = ['publicStaticIterableProperty'];
    public static object $publicStaticObjectProperty;
    public static ?object $publicStaticNullableObjectProperty;
    public static EmptyClass $publicStaticClassProperty;
    public static ?EmptyClass $publicStaticNullableClassProperty;

    protected static $protectedStaticUnTypedProperty               = 'protectedStaticUnTypedProperty';
    protected static bool $protectedStaticBoolProperty               = true;
    protected static ?bool $protectedStaticNullableBoolProperty       = true;
    protected static int $protectedStaticIntProperty                = 123;
    protected static ?int $protectedStaticNullableIntProperty        = 123;
    protected static float $protectedStaticFloatProperty              = 123.456;
    protected static ?float $protectedStaticNullableFloatProperty      = 123.456;
    protected static string $protectedStaticStringProperty             = 'protectedStaticStringProperty';
    protected static ?string $protectedStaticNullableStringProperty     = 'protectedStaticStringProperty';
    protected static array $protectedStaticArrayProperty              = ['protectedStaticArrayProperty'];
    protected static ?array $protectedStaticNullableArrayProperty      = ['protectedStaticArrayProperty'];
    protected static iterable $protectedStaticIterableProperty           = ['protectedStaticIterableProperty'];
    protected static ?iterable $protectedStaticNullableIterableProperty   = ['protectedStaticIterableProperty'];
    protected static object $protectedStaticObjectProperty;
    protected static ?object $protectedStaticNullableObjectProperty;
    protected static EmptyClass $protectedStaticClassProperty;
    protected static ?EmptyClass $protectedStaticNullableClassProperty;

    private static $privateStaticUnTypedProperty               = 'privateStaticUnTypedProperty';
    private static bool $privateStaticBoolProperty               = true;
    private static ?bool $privateStaticNullableBoolProperty       = true;
    private static int $privateStaticIntProperty                = 123;
    private static ?int $privateStaticNullableIntProperty        = 123;
    private static float $privateStaticFloatProperty              = 123.456;
    private static ?float $privateStaticNullableFloatProperty      = 123.456;
    private static string $privateStaticStringProperty             = 'privateStaticStringProperty';
    private static ?string $privateStaticNullableStringProperty     = 'privateStaticStringProperty';
    private static array $privateStaticArrayProperty              = ['privateStaticArrayProperty'];
    private static ?array $privateStaticNullableArrayProperty      = ['privateStaticArrayProperty'];
    private static iterable $privateStaticIterableProperty           = ['privateStaticIterableProperty'];
    private static ?iterable $privateStaticNullableIterableProperty   = ['privateStaticIterableProperty'];
    private static object $privateStaticObjectProperty;
    private static ?object $privateStaticNullableObjectProperty;
    private static EmptyClass $privateStaticClassProperty;
    private static ?EmptyClass $privateStaticNullableClassProperty;

    public $publicUnTypedProperty               = 'publicUnTypedProperty';
    public bool $publicBoolProperty               = true;
    public ?bool $publicNullableBoolProperty       = true;
    public int $publicIntProperty                = 123;
    public ?int $publicNullableIntProperty        = 123;
    public float $publicFloatProperty              = 123.456;
    public ?float $publicNullableFloatProperty      = 123.456;
    public string $publicStringProperty             = 'publicStringProperty';
    public ?string $publicNullableStringProperty     = 'publicStringProperty';
    public array $publicArrayProperty              = ['publicArrayProperty'];
    public ?array $publicNullableArrayProperty      = ['publicArrayProperty'];
    public iterable $publicIterableProperty           = ['publicIterableProperty'];
    public ?iterable $publicNullableIterableProperty   = ['publicIterableProperty'];
    public object $publicObjectProperty;
    public ?object $publicNullableObjectProperty;
    public EmptyClass $publicClassProperty;
    public ?EmptyClass $publicNullableClassProperty;

    protected $protectedUnTypedProperty               = 'protectedUnTypedProperty';
    protected bool $protectedBoolProperty               = true;
    protected ?bool $protectedNullableBoolProperty       = true;
    protected int $protectedIntProperty                = 123;
    protected ?int $protectedNullableIntProperty        = 123;
    protected float $protectedFloatProperty              = 123.456;
    protected ?float $protectedNullableFloatProperty      = 123.456;
    protected string $protectedStringProperty             = 'protectedStringProperty';
    protected ?string $protectedNullableStringProperty     = 'protectedStringProperty';
    protected array $protectedArrayProperty              = ['protectedArrayProperty'];
    protected ?array $protectedNullableArrayProperty      = ['protectedArrayProperty'];
    protected iterable $protectedIterableProperty           = ['protectedIterableProperty'];
    protected ?iterable $protectedNullableIterableProperty   = ['protectedIterableProperty'];
    protected object $protectedObjectProperty;
    protected ?object $protectedNullableObjectProperty;
    protected EmptyClass $protectedClassProperty;
    protected ?EmptyClass $protectedNullableClassProperty;

    private $privateUnTypedProperty               = 'privateUnTypedProperty';
    private bool $privateBoolProperty               = true;
    private ?bool $privateNullableBoolProperty       = true;
    private int $privateIntProperty                = 123;
    private ?int $privateNullableIntProperty        = 123;
    private float $privateFloatProperty              = 123.456;
    private ?float $privateNullableFloatProperty      = 123.456;
    private string $privateStringProperty             = 'privateStringProperty';
    private ?string $privateNullableStringProperty     = 'privateStringProperty';
    private array $privateArrayProperty              = ['privateArrayProperty'];
    private ?array $privateNullableArrayProperty      = ['privateArrayProperty'];
    private iterable $privateIterableProperty           = ['privateIterableProperty'];
    private ?iterable $privateNullableIterableProperty   = ['privateIterableProperty'];
    private object $privateObjectProperty;
    private ?object $privateNullableObjectProperty;
    private EmptyClass $privateClassProperty;
    private ?EmptyClass $privateNullableClassProperty;
}
