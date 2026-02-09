<?php

declare(strict_types=1);

namespace Ipedis\HttpSignature\Tests\Unit\Signature;

use Ipedis\HttpSignature\Signature\SigningString;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class SigningStringTest extends TestCase
{
    private Psr17Factory $psr17Factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->psr17Factory = new Psr17Factory();
    }

    public function test_signing_string_format(): void
    {
        $request = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');
        $request = $request->withBody($this->psr17Factory->createStream('{"test": "data"}'));

        $timestamp = 1234567890;
        $signingString = new SigningString($request, $timestamp);

        $expected = 'POST.https://example.com/api/test.1234567890.{"test": "data"}';
        $this->assertEquals($expected, $signingString->string());
    }

    public function test_signing_string_removes_trailing_slash(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://example.com/api/test/');

        $timestamp = 1234567890;
        $signingString = new SigningString($request, $timestamp);

        $expected = 'GET.https://example.com/api/test.1234567890.';
        $this->assertEquals($expected, $signingString->string());
    }

    public function test_signing_string_to_string(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://example.com/api/test');

        $timestamp = 1234567890;
        $signingString = new SigningString($request, $timestamp);

        $this->assertEquals($signingString->string(), (string) $signingString);
    }

    public function test_signing_string_with_empty_body(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://example.com/api/test');

        $timestamp = 1234567890;
        $signingString = new SigningString($request, $timestamp);

        $expected = 'GET.https://example.com/api/test.1234567890.';
        $this->assertEquals($expected, $signingString->string());
    }

    public function test_signing_string_with_different_methods(): void
    {
        $timestamp = 1234567890;

        $getRequest = $this->psr17Factory->createRequest('GET', 'https://example.com/api/test');
        $getSigningString = new SigningString($getRequest, $timestamp);

        $postRequest = $this->psr17Factory->createRequest('POST', 'https://example.com/api/test');
        $postSigningString = new SigningString($postRequest, $timestamp);

        $this->assertStringStartsWith('GET.', $getSigningString->string());
        $this->assertStringStartsWith('POST.', $postSigningString->string());
        $this->assertNotEquals($getSigningString->string(), $postSigningString->string());
    }
}
