<?php
/*

Copyright 2022 Juan Sebastián Echeverry (baxtian.echeverry@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

namespace ScssLibrary\Libs;

use ScssLibrary\Libs\ScssLibrary;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;
use ScssPhp\ScssPhp\CompilationResult;
use Exception;

/**
 * Class to compile a scss code.
 */
class ScssCompiler
{
	/**
	 * Compile the data. This creates a map file
	 *
	 * @param ScssLibrary $scss_library	Element to be compiled
	 * @return CompilationResult				Array with CSS and MAP
	 */
	public function compile(ScssLibrary $scss_library)
	{
		// Compile from SCSS to CSS
		try {
			// Format type. Expanded if in development mode, compressed in any other case.
			$formatter = ($scss_library->dev_mode) ? OutputStyle::EXPANDED : OutputStyle::COMPRESSED;

			// Initialize compiler
			$compiler = new Compiler();

			// Create map if in development mode
			if ($scss_library->dev_mode) {
				$srcmap_data = [
					// Absolute path to the map file
					'sourceMapWriteTo' => $scss_library->out_file . '.map',
					// URL to the map file
					'sourceMapURL' => $scss_library->out_url . '.map',
					// Partial route to use a root
					'sourceMapBasepath' => rtrim(ABSPATH, '/'),
					// Where to redirect external files
					'sourceRoot' => dirname(content_url()),
				];

				// Configuration to create the debugging .map file.
				$compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
				$compiler->setSourceMapOptions($srcmap_data);
			}

			// Configuration to initialize the compiler.
			$compiler->setOutputStyle($formatter);
			$compiler->addVariables($scss_library->variables);
			$compiler->setImportPaths(dirname($scss_library->in_file));

			// Get file contents
			if(!file_exists($scss_library->in_file)) {
				throw new Exception(__("File does not exists.", "scsslib"), Errors::SOURCE_NOT_FOUND);
			}

			$stylesheet = file_get_contents($scss_library->in_file);

		} catch (Exception $e) {
			// In case of error throw the same
			throw $e;
		}

		return $compiler->compileString($stylesheet, $scss_library->in_file);
	}

	/**
	 * Detect if a compilations is required.
	 * Is the timestamp of the source file newer than the timestamp of
	 * creation of the compiled version?
	 * Is the array of variables used to create the compiled file
	 * different from the current one?
	 * Doesn't exists the compiled file yet?
	 *
	 * @param string 		$in
	 * @param string 		$out
	 * @param array  		$variables
	 * @param array|false  	$filemtimes
	 * @return bool			Indicate if the file has to be compiled
	 */
	public function compilation_is_required(string $in, string $out, array $variables, &$filemtimes): bool
	{
		// If the filemtimes isn't already craeated, create an empty array to store timestamps
		if (!is_array($filemtimes) || $filemtimes === false) {
			$filemtimes = [];
		}

		// It compares the creation date of the compiled file with the creation date
		// of the source file. If it is more recent then it needs to be compiled.
		if (isset($filemtimes[$out]) === false || $filemtimes[$out] < filemtime($in)) {
			return true;
		}

		// If the variables do not match then you have to compile
		$signature = sha1(serialize($variables));
		$sha = sha1($out);
		if ($signature !== get_transient('scsslib_variables_signature_' . $sha)) {
			set_transient('scsslib_variables_signature_' . $sha, $signature);

			return true;
		}

		// If the file does not exist then you have to compile
		if (!file_exists($out)) {
			return true;
		}

		return false;
	}
}
