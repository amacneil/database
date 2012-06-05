<?php

use Mockery as m;

class ConnectionTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testSettingDefaultCallsGetDefaultGrammar()
	{
		$connection = $this->getMock('Illuminate\Database\Connection', array('getDefaultQueryGrammar'), array(new MockPDO));
		$connection->expects($this->once())->method('getDefaultQueryGrammar')->will($this->returnValue('foo'));
		$connection->useDefaultQueryGrammar();
		$this->assertEquals('foo', $connection->getQueryGrammar());
	}


	public function testSelectOneCallsSelectAndReturnsSingleResult()
	{
		$connection = $this->getMockConnection(array('select'));
		$connection->expects($this->once())->method('select')->with('foo', array('bar' => 'baz'))->will($this->returnValue(array('foo')));
		$this->assertEquals('foo', $connection->selectOne('foo', array('bar' => 'baz')));
	}


	public function testSelectProperlyCallsPDO()
	{
		$pdo = $this->getMock('MockPDO', array('prepare'));
		$statement = $this->getMock('PDOStatement', array('execute', 'fetchAll'));
		$statement->expects($this->once())->method('execute')->with($this->equalTo(array('foo' => 'bar')));
		$statement->expects($this->once())->method('fetchAll')->will($this->returnValue(array('boom')));
		$pdo->expects($this->once())->method('prepare')->with('foo')->will($this->returnValue($statement));
		$mock = $this->getMockConnection(array(), $pdo);
		$results = $mock->select('foo', array('foo' => 'bar'));
		$this->assertEquals(array('boom'), $results);
	}


	protected function getMockConnection($methods = array(), $pdo = null)
	{
		$pdo = $pdo ?: new MockPDO;
		return $this->getMock('Illuminate\Database\Connection', array_merge(array('getDefaultQueryGrammar'), $methods), array($pdo));
	}

}

class MockPDO extends PDO { public function __construct() {} }