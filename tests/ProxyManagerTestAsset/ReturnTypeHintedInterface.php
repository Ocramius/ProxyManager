<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

interface ReturnTypeHintedInterface
{
    public function returnString() : string;

    public function returnInteger() : int;

    public function returnBool() : bool;

    public function returnFloat() : float;

    public function returnArray() : array;

    public function returnCallable() : callable;

    public function returnSelf() : self;

    public function returnIterable() : iterable;

    public function returnObject() : object;

    public function returnVoid() : void;

    public function returnSameClass() : ReturnTypeHintedInterface;

    public function returnOtherClass() : EmptyClass;
}
