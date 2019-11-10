<?php

use Clue\PharComposer\Package\Bundle;
use Clue\PharComposer\Phar\TargetPhar;
use Clue\PharComposer\Package\Package;

class TargetPharTest extends TestCase
{
    /**
     * instance to test
     *
     * @ype  TargetPhar
     */
    private $targetPhar;

    private $mockPhar;

    private $mockBox;

    private $mockPharComposer;

    /**
     * set up test environment
     */
    public function setUp()
    {
        if (PHP_VERSION_ID >= 50400 && PHP_VERSION_ID <= 50600) {
            $this->markTestSkipped('Unable to mock \Phar on PHP 5.4/5.5');
        }

        $this->mockPhar = $this->createMock('\Phar');
        $this->mockBox  = $this->createMock('Herrera\Box\Box');
        $this->mockBox->expects($this->any())
                      ->method('getPhar')
                      ->will($this->returnValue($this->mockPhar));
        $this->mockPharComposer = $this->createMock('Clue\PharComposer\Phar\PharComposer');
        $this->targetPhar       = new TargetPhar($this->mockBox, $this->mockPharComposer);
    }

    private function createMock($class)
    {
        return $this->getMockBuilder($class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * @test
     */
    public function addFileCalculatesLocalPartForBox()
    {
        $this->mockPharComposer->expects($this->once())
                               ->method('getPathLocalToBase')
                               ->with($this->equalTo('path/to/package/file.php'))
                               ->will($this->returnValue('file.php'));
        $this->mockBox->expects($this->once())
                      ->method('addFile')
                      ->with($this->equalTo('path/to/package/file.php'), $this->equalTo('file.php'));
        $this->targetPhar->addFile('path/to/package/file.php');
    }

    /**
     * @test
     */
    public function buildFromIteratorProvidesBasePathForBox()
    {
        $mockPackage = new Package(array(), 'path/to/package');
        $mockTraversable = $this->getMock('\Iterator');
        $this->mockPharComposer->expects($this->once())
                               ->method('getPackageRoot')
                               ->willReturn($mockPackage);
        $this->mockBox->expects($this->once())
                      ->method('buildFromIterator')
                      ->with($this->equalTo($mockTraversable), $this->equalTo('path/to/package/'));
        $this->targetPhar->buildFromIterator($mockTraversable);
    }

    /**
     * @test
     */
    public function addPackageAddsResourcesFromCalculatedBundle()
    {
        $bundle = new Bundle();
        $bundle->addFile('path/to/package/file.php');
        $this->mockPharComposer->expects($this->once())
                               ->method('getPathLocalToBase')
                               ->with($this->equalTo('path/to/package/file.php'))
                               ->will($this->returnValue('file.php'));
        $this->mockBox->expects($this->once())
                      ->method('addFile')
                      ->with($this->equalTo('path/to/package/file.php'), $this->equalTo('file.php'));
        $mockFinder = $this->createMock('Symfony\Component\Finder\Finder');
        $bundle->addDir($mockFinder);
        $mockPackage = new Package(array(), 'path/to/package');
        $this->mockPharComposer->expects($this->once())
                               ->method('getPackageRoot')
                               ->willReturn($mockPackage);
        $this->mockBox->expects($this->once())
                      ->method('buildFromIterator')
                      ->with($this->equalTo($mockFinder), $this->equalTo('path/to/package/'));
        $this->targetPhar->addBundle($bundle);
    }

    /**
     * @test
     */
    public function setsStubOnUnderlyingPhar()
    {
        $this->mockPhar->expects($this->once())
                       ->method('setStub')
                       ->with($this->equalTo('some stub code'));
        $this->targetPhar->setStub('some stub code');
    }

    /**
     * @test
     */
    public function finalizeStopsBufferingOnUnderlyingPhar()
    {
        $this->mockPhar->expects($this->once())
                       ->method('stopBuffering');
        $this->targetPhar->finalize();
    }
}