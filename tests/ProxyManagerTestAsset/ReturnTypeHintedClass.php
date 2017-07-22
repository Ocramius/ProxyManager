<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

class ReturnTypeHintedClass extends EmptyClass
{
    public function returnString() : string
    {
    }

    public function returnInteger() : int
    {
    }

    public function returnBool() : bool
    {
    }

    public function returnFloat() : float
    {
    }

    public function returnArray() : array
    {
    }

    public function returnCallable() : callable
    {
    }

    public function returnSelf() : self
    {
    }

    public function returnParent() : parent
    {
    }

    public function returnVoid() : void
    {
    }

    public function returnIterable() : iterable
    {
    }

    public function returnObject() : object
    {
    }

    public function returnSameClass() : ReturnTypeHintedClass
    {
    }

    public function returnOtherClass() : EmptyClass
    {
    }
}
