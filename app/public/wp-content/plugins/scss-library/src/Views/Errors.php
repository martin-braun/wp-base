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

namespace ScssLibrary\Views;

use ScssLibrary\Libs\Errors as Data;

/**
 * Class to add scss style files directly with the wp_enqueue_style function.
 */
class Errors
{
	/**
	 * Render the error list in an attention-grabbing format.
	 *
	 * @param array $errors
	 * @return void
	 */
	public static function render(array $errors): void
	{
		?>
		<style>
		#scsslib {
			position: fixed;
			top: 0;
			z-index: 99999;
			width: 100%;
			padding: 20px;
			overflow: auto;
			background: #f5f5f5;
			font-family: 'Source Code Pro', Menlo, Monaco, Consolas, monospace;
			font-size: 18px;
			color: #666;
			text-align: left;
			border-left: 5px solid #DD3D36;
		}
		body.admin-bar #scsslib {
			top: 32px;
		}
		#scsslib .scsslib-title {
			margin-bottom: 20px;
			font-size: 120%;
		}
		#scsslib .scsslib-error {
			margin: 10px 0;
		}
		#scsslib .scsslib-file {
			font-weight: bold;
			white-space: pre;
			white-space: pre-wrap;
			word-wrap: break-word;
		}
		#scsslib .scsslib-message {
			white-space: pre;
			white-space: pre-wrap;
			word-wrap: break-word;
		}
		</style>
		<div id="scsslib">
			<div class="scsslib-title"><?php _e('Sass Compiling Error', 'scsslib'); ?></div>
			<?php foreach ($errors as $error): ?>
				<div class="scsslib-error">
					<div class="scsslib-file"><?php if ($error['handle']) {
			printf('%s : ', $error['handle']);
		} ?><?php print $error['file'] ?></div>
					<div class="scsslib-message"><?php print $error['message'] ?></div>
				</div>
			<?php endforeach ?>
		</div>
		<?php
	}

	/**
	 * If there are any errors, render the list in the footer.
	 *
	 * @return void
	 */
	public static function wp_footer(): void
	{
		$data = Data::get_instance();

		// In case there are error logs, display them.
		if (count($data->errors) > 0) {
			self::render($data->errors);
		}
	}
}
