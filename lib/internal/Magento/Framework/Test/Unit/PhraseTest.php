<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use \Magento\Framework\Phrase;

class PhraseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Phrase\RendererInterface
     */
    protected $defaultRenderer;

    /**
     * @var \Magento\Framework\Phrase\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rendererMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp()
    {
        $this->defaultRenderer = Phrase::getRenderer();
        $this->rendererMock = $this->getMockBuilder('Magento\Framework\Phrase\RendererInterface')
            ->getMock();
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * Tear down
     *
     * @return void
     */
    protected function tearDown()
    {
        Phrase::setRenderer($this->defaultRenderer);
    }

    /**
     * Test rendering
     *
     * @return void
     */
    public function testRendering()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $result = 'rendered text';
        $phrase = $this->objectManager->getObject('Magento\Framework\Phrase', [
            'text' => $text,
            'arguments' => $arguments,
        ]);
        Phrase::setRenderer($this->rendererMock);

        $this->rendererMock->expects($this->once())
            ->method('render')
            ->with([$text], $arguments)
            ->willReturn($result);

        $this->assertEquals($result, $phrase->render());
    }

    /**
     * Test defers rendering
     *
     * @return void
     */
    public function testDefersRendering()
    {
        $this->rendererMock->expects($this->never())
            ->method('render');

        $this->objectManager->getObject('Magento\Framework\Phrase', [
            'text' => 'some text',
        ]);
    }

    /**
     * Test that to string is alias to render
     *
     * @return void
     */
    public function testThatToStringIsAliasToRender()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $result = 'rendered text';
        $phrase = $this->objectManager->getObject('Magento\Framework\Phrase', [
            'text' => $text,
            'arguments' => $arguments,
        ]);
        Phrase::setRenderer($this->rendererMock);

        $this->rendererMock->expects($this->once())
            ->method('render')
            ->with([$text], $arguments)
            ->willReturn($result);

        $this->assertEquals($result, (string)$phrase);
    }

    /**
     * Test get text
     *
     * @return void
     */
    public function testGetText()
    {
        $text = 'some text';
        $phrase = new Phrase($text);
        $phrase = $this->objectManager->getObject('Magento\Framework\Phrase', [
            'text' => $text,
        ]);

        $this->assertEquals($text, $phrase->getText());
    }

    /**
     * Test get arguments
     *
     * @return void
     */
    public function testGetArguments()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $phrase1 = $this->objectManager->getObject('Magento\Framework\Phrase', [
            'text' => $text,
        ]);
        $phrase2 = $this->objectManager->getObject('Magento\Framework\Phrase', [
            'text' => $text,
            'arguments' => $arguments,
        ]);

        $this->assertEquals([], $phrase1->getArguments());
        $this->assertEquals($arguments, $phrase2->getArguments());
    }

    /**
     * Test default rendering
     *
     * @return void
     */
    public function testDefaultRendering()
    {
        $text = 'parameter1 is replaced by %1 parameter2 is replaced by %2';
        $arguments = ['arg1', 'arg2'];
        $result = 'parameter1 is replaced by arg1 parameter2 is replaced by arg2';
        $phrase = $this->objectManager->getObject('Magento\Framework\Phrase', [
            'text' => $text,
            'arguments' => $arguments,
        ]);

        $this->assertEquals($text, $phrase->getText());
        $this->assertEquals($arguments, $phrase->getArguments());
        $this->assertTrue($phrase->getRenderer() instanceof \Magento\Framework\Phrase\Renderer\Placeholder);
        $this->assertEquals($result, $phrase->render());
    }
}
