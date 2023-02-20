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

use ScssLibrary\Libs\ScssCompiler;
use ScssLibrary\Libs\Errors;
use ScssLibrary\Libs\CheckDirectory;
use ScssLibrary\Libs\DevMode;
use Exception;

/**
 * Class to filter a style file and create the variables to compile if required.
 */
class ScssLibrary
{
	use \Baxtian\SingletonTrait;

	// Directory where compiled files will be written
	private $build_dir; // Path
	private $build_url; // URL

	// Variables para la compilación
	private $in_file;
	private $out_url;
	private $out_file;
	private $variables;

	// Filemtimes
	private $filemtimes;

	// Development mode
	private $dev_mode;

	// Flag to detect if compiled
	private $compiled;

	protected function __construct($arguments = [])
	{
		// Dependencies
		$classes = [
			'errors'   => Errors::class,
			'compiler' => ScssCompiler::class,
			'check_directory' => CheckDirectory::class,
			'dev_mode' => DevMode::class,
		];
		$this->set_dependencies($arguments, $classes);

		// Dev mode
		$this->dev_mode = $this->dependency('dev_mode')->is_active();

		// Directories
		$this->set_directory();
	}

	/**
	 * Magic method to get the private variables.
	 *
	 * @param string $name			Name of the variable to get
	 * @return boolean|array|string	Value of the variable
	 */
	public function __get(string $name)
	{
		$answer = null;
		switch ($name) {
			case 'dev_mode':
			case 'in_file':
			case 'out_url':
			case 'out_file':
			case 'variables':
			case 'build_dir':
			case 'build_url':
			case 'compiled':
				$answer = $this->$name;

				break;
		}

		return $answer;
	}

	/**
	 * Set the directory where the compiled files will be stored.
	 *
	 * @return void
	 */
	private function set_directory(): void
	{
		// Directory where the cache will be stored
		$pathname = '/build/scss_library/';
		if (is_multisite()) {
			$blog_id = get_current_blog_id();
			$pathname .= $blog_id . '/';
		}

		$this->build_dir = WP_CONTENT_DIR . $pathname;
		$this->build_url = WP_CONTENT_URL . $pathname;
	}

	/**
	 * Function to service styles and compile those with SCSS extension
	 * @param  string $src    URL of the file to be served
	 * @param  string $handle Name by which it is identified internally
	 * @return string         URL to the copiled version or to the original in case it has not been compiled
	 */
	public function style_loader_src($src, $handle): string
	{
		// Set compiled flag as false
		$this->compiled = false;
		
		// Do not follow if there are problems with the directory
		if (!$this->dependency('check_directory')->check($this->build_dir)) {
			// Delete the record of creation timestamps.
			delete_transient('scsslib_filemtimes');

			// Return the src file as answer because we can't create the compiled version
			return $src;
		}

		// Parse the URL of the style file
		$url       = parse_url($src);
		$path_info = pathinfo($url['path']);

		// Detailed review to determine if the extension corresponds
		if (isset($path_info['extension']) && $path_info['extension'] !== 'scss') {
			return $src;
		}

		// Convert the URL to absolute paths.
		$in = preg_replace('/^' . preg_quote(site_url(), '/') . '/i', '', $src);

		// Ignoring SCSS from CDNs, other domains and relative paths
		// If it includes // or the initial element
		if (preg_match('#^//#', $in) || strpos($in, '/') !== 0) {
			return $src;
		}

		// Create complete route
		$this->in_file = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $url['path'];

		// Create complete route
		if (is_multisite()) {
			$aux               = get_blog_details();
			$blog_details_path = $aux->path;
			if ($blog_details_path != PATH_CURRENT_SITE) {
				$this->in_file = str_replace($blog_details_path, PATH_CURRENT_SITE, $this->in_file);
			}
		}

		// Confirm that the source file exists
		if (file_exists($this->in_file) === false) {
			$this->dependency('errors')->enqueue(
				$handle,
				basename($this->in_file),
				__('Source file not found.', 'scsslib'),
				Errors::SOURCE_NOT_FOUND
			);

			return $src;
		}

		// Generate unique name for the compiled file
		$out_name = sha1($url['path']) . '.css';

		// Path and URL to the compiled file
		$this->out_file = $this->build_dir . $out_name;
		$this->out_url  = $this->build_url . $out_name;

		// Obtain the variables
		$this->variables = apply_filters('scsslib_compiler_variables', [
			'template_directory_uri'   => get_template_directory_uri(),
			'stylesheet_directory_uri' => get_stylesheet_directory_uri(),
		]);

		// Times of creation
		$filemtimes = get_transient('scsslib_filemtimes');

		// Should we compile or not?
		// 	In dev mode?
		//  Action required in URL?
		// 	Compilation is required?
		if (
			$this->dev_mode ||
			$this->dependency('dev_mode')->required_action() ||
			$this->dependency('compiler')->compilation_is_required($this->in_file, $this->out_file, $this->variables, $filemtimes)
		) {
			try {
				$compiler = $this->dependency('compiler');
				$result   = $compiler->compile($this);

				// Transforming relative paths to work correctly
				$css = preg_replace('#(url\((?![\'"]?(?:https?:|/))[\'"]?)#miu', '$1' . dirname($url['path']) . '/', $result->getCss());

				// Save the compiled file.
				file_put_contents($this->out_file, $css);

				// Save map if a source map has been created
				if ($map = $result->getSourceMap()) {
					file_put_contents($this->out_file . '.map', $map);
				} elseif (file_exists($this->out_file . '.map')) { // Delete if file exists
					unlink($this->out_file . '.map');
				}

				// Save the file creation time.
				$filemtimes[$this->out_file] = filemtime($this->out_file);
				set_transient('scsslib_filemtimes', $filemtimes);

				// Set compiledflag as true;
				$this->compiled = true;
			} catch (Exception $e) {
				$this->dependency('errors')->enqueue(
					$handle,
					basename($this->in_file),
					$e->getMessage(),
					$e->getCode()
				);

				return $src;
			}
		}

		// Construct URL of the compiled file with the query strings
		// that came in the URL of the source file.
		return empty($url['query']) ? $this->build_url . $out_name : $this->build_url . $out_name . '?' . $url['query'];
	}
}
