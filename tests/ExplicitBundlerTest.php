<?php

use Clue\PharComposer\Bundler\Explicit as ExplicitBundler;

class ExplicitBundlerTest extends TestCase
{
    /**
     * instance to test
     *
     * @type  ExplicitBundler
     */
    private $explicitBundler;

    private $mockTargetPhar;

    private $mockPharComposer;

    private $mockPackage;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->mockTargetPhar   = $this->createMock('Clue\PharComposer\TargetPhar');
        $this->mockPharComposer = $this->createMock('Clue\PharComposer\PharComposer');
        $this->mockPackage      = $this->createMock('Clue\PharComposer\Package');
        $this->explicitBundler  = new ExplicitBundler($this->mockPackage);
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
    public function addsBinariesDefinedByPackageToBox()
    {
        $this->mockPackage->expects($this->once())
                          ->method('getBins')
                          ->will($this->returnValue(array('bin/example')));
        $this->mockTargetPhar->expects($this->once())
                             ->method('addFile')
                             ->with($this->equalTo('bin/example'));
        $this->explicitBundler->build($this->mockPharComposer, $this->mockTargetPhar);
    }

    /**
     * @test
     */
    public function addsFilesDefinedByAutoload()
    {
        $this->mockPackage->expects($this->once())
                          ->method('getBins')
                          ->will($this->returnValue(array()));
        $this->mockPackage->expects($this->once())
                          ->method('getAutoload')
                          ->will($this->returnValue(array('files' => array('foo.php'))));
        $this->mockPackage->expects($this->once())
                          ->method('getAbsolutePath')
                          ->with($this->equalTo('foo.php'))
                          ->will($this->returnValue('foo.php'));
        $this->mockTargetPhar->expects($this->once())
                             ->method('addFile')
                             ->with($this->equalTo('foo.php'));
        $this->explicitBundler->build($this->mockPharComposer, $this->mockTargetPhar);
    }

    /**
     * @test
     */
    public function addsFilesDefinedByClassmap()
    {
        $this->mockPackage->expects($this->once())
                          ->method('getBins')
                          ->will($this->returnValue(array()));
        $this->mockPackage->expects($this->once())
                          ->method('getAutoload')
                          ->will($this->returnValue(array('classmap' => array('Example/SomeClass.php'))));
        $this->mockPackage->expects($this->once())
                          ->method('getAbsolutePath')
                          ->with($this->equalTo('Example/SomeClass.php'))
                          ->will($this->returnValue('src/Example/SomeClass.php'));
        $this->mockTargetPhar->expects($this->once())
                             ->method('addFile')
                             ->with($this->equalTo('src/Example/SomeClass.php'));
        $this->explicitBundler->build($this->mockPharComposer, $this->mockTargetPhar);
    }

    /**
     * @test
     */
    public function addsDirectoriesDefinedByClassmap()
    {
        $this->mockPackage->expects($this->once())
                          ->method('getBins')
                          ->will($this->returnValue(array()));
        $this->mockPackage->expects($this->once())
                          ->method('getAutoload')
                          ->will($this->returnValue(array('classmap' => array(__DIR__))));
        $this->mockPackage->expects($this->once())
                          ->method('getAbsolutePath')
                          ->with($this->equalTo(__DIR__))
                          ->will($this->returnValue(__DIR__));
        $this->mockTargetPhar->expects($this->once())
                             ->method('buildFromIterator');
        $this->explicitBundler->build($this->mockPharComposer, $this->mockTargetPhar);
    }

    /**
     * @test
     */
    public function addsAllPathesDefinedByPsr0WithSinglePath()
    {
        $this->mockPackage->expects($this->once())
                          ->method('getBins')
                          ->will($this->returnValue(array()));
        $path = realpath(__DIR__ . '/../src');
        $this->mockPackage->expects($this->once())
                          ->method('getAutoload')
                          ->will($this->returnValue(array('psr-0' => array('Clue' => $path))));
        $this->mockPackage->expects($this->once())
                          ->method('getAbsolutePath')
                          ->with($this->equalTo($path . '/Clue'))
                          ->will($this->returnValue($path . '/Clue'));
        $this->mockTargetPhar->expects($this->once())
                             ->method('buildFromIterator');
        $this->explicitBundler->build($this->mockPharComposer, $this->mockTargetPhar);
    }

    /**
     * @test
     */
    public function addsAllPathesDefinedByPsr0WithSeveralPathes()
    {
        $this->mockPackage->expects($this->once())
                          ->method('getBins')
                          ->will($this->returnValue(array()));
        $path = realpath(__DIR__ . '/../src');
        $this->mockPackage->expects($this->once())
                          ->method('getAutoload')
                          ->will($this->returnValue(array('psr-0' => array('Clue' => array($path, $path)))));
        $this->mockPackage->expects($this->exactly(2))
                          ->method('getAbsolutePath')
                          ->with($this->equalTo($path . '/Clue'))
                          ->will($this->returnValue($path . '/Clue'));
        $this->mockTargetPhar->expects($this->exactly(2))
                             ->method('buildFromIterator');
        $this->explicitBundler->build($this->mockPharComposer, $this->mockTargetPhar);
    }
}