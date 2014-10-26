<?php
namespace Core\Lib\Data;

/**
 *
 * @author Michael
 *
 */
interface AdapterInterface
{
	public function create();

	public function read();

	public function update();

	public function delete();
}
