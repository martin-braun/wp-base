<?php


namespace As247\CloudStorages\Contracts\Storage;


use As247\CloudStorages\Exception\FileNotFoundException;

interface ObjectStorage
{
	/**
	 * @param string $urn the unified resource name used to identify the object
	 * @return resource stream with the read data
	 * @throws \Exception when something goes wrong, message will be logged
	 * @throws FileNotFoundException if file does not exist
	 * @since 1.0.15
	 */
	public function readObject($urn);

	/**
	 * @param string $urn the unified resource name used to identify the object
	 * @param resource $stream stream with the data to write
	 * @throws \Exception when something goes wrong, message will be logged
	 * @since 1.0.15
	 */
	public function writeObject($urn, $stream);

	/**
	 * @param string $urn the unified resource name used to identify the object
	 * @return void
	 * @throws \Exception when something goes wrong, message will be logged
	 * @since 1.0.15
	 */
	public function deleteObject($urn);

	/**
	 * Check if an object exists in the object store
	 *
	 * @param string $urn
	 * @return bool
	 * @since 1.0.15
	 */
	public function objectExists($urn);
}
