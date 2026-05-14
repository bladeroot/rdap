<?php

declare(strict_types=1);

namespace hiqdev\rdap\core\tests\unit\Domain\ValueObject\Label;

use hiqdev\rdap\core\Domain\ValueObject\Label\Label;
use hiqdev\rdap\core\Domain\ValueObject\Label\LDHLabel;
use hiqdev\rdap\core\Domain\ValueObject\Label\NonASCIILabel;
use hiqdev\rdap\core\Domain\ValueObject\Label\RootLabel;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;

class LabelTest extends TestCase
{
    public function testEmptyStringReturnsRootLabel(): void
    {
        $label = Label::of('');
        $this->assertInstanceOf(RootLabel::class, $label);
        $this->assertSame('', (string) $label);
    }

    public function testASCIIStringReturnsLDHLabel(): void
    {
        $label = Label::of('example');
        $this->assertInstanceOf(LDHLabel::class, $label);
        $this->assertSame('example', (string) $label);
    }

    public function testNonASCIIStringReturnsNonASCIILabel(): void
    {
        $label = Label::of('пример');
        $this->assertInstanceOf(NonASCIILabel::class, $label);
        $this->assertSame('пример', (string) $label);
    }

    public function testLDHLabelWithDigitsAndHyphen(): void
    {
        $label = Label::of('xn--nxasmq6b');
        $this->assertInstanceOf(LDHLabel::class, $label);
    }

    public function testLDHLabelInvalidCharThrows(): void
    {
        $this->expectException(OutOfRangeException::class);
        new LDHLabel('invalid!label');
    }

    public function testRootLabelIsSingleton(): void
    {
        $a = RootLabel::getInstance();
        $b = RootLabel::getInstance();
        $this->assertSame($a, $b);
    }

    public function testLabelToLDH(): void
    {
        $label = Label::of('пример');
        $ldh   = $label->toLDH();
        $this->assertInstanceOf(LDHLabel::class, $ldh);
        $this->assertStringStartsWith('xn--', (string) $ldh);
    }

    public function testLabelToUnicode(): void
    {
        $label   = Label::of('xn--e1afmkfd');
        $unicode = $label->toUnicode();
        $this->assertSame('пример', (string) $unicode);
    }

    public function testGetValue(): void
    {
        $label = Label::of('com');
        $this->assertSame('com', $label->getValue());
    }
}
