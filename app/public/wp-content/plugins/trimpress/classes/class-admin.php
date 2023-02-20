<?php
/**
 * The file that defines the admin class.
 *
 * @package    TrimPress
 * @subpackage TrimPress/classes
 */

// Define the plugin namespace.
Namespace TrimPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin class.
 *
 * @package    TrimPress
 * @subpackage TrimPress/classes
 */
class Admin {

	/**
	 * The class constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'create_submenu' ) );
    }
    
    /**
	 * Create the submenu page.
	 */
    public function create_submenu() {
        add_submenu_page(
            'options-general.php',
            __( 'TrimPress Settings', 'trimpress' ),
            'TrimPress',
            'manage_options',
            'trimpress',
            array( $this, 'admin_html' )
        );
    }
	
	/**
	 * Callback to render the submenu page markup.
	 */
    public function admin_html() {
    
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
		}

        ?>

		<style>

		.trimpress-admin p {
			font-size: 14px;
		}

		.trimpress-admin hr {
			margin-bottom: 1rem;
		}

		.safety {
			display: inline-block;
			width: 0.2rem;
			height: 1rem;
			margin-bottom: -0.25rem;
			border-radius: 0.05rem;
		}

		.safety.green {
			background-color: #00A32A;
		}

		.safety.blue {
			background-color: #00A0D2;
		}

		.trimpress-admin .button-secondary {
			margin-bottom: 0;
		}

		#submit1 {
			position: absolute;
			margin: -47px 0 0 150px;
		}

		@media (min-width: 783px) {
			#submit1 {
				position: absolute;
				margin: -37px 0 0 134px;
			}
		}

		</style>

        <div class="wrap trimpress-admin">
        	
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<p>
				
				<span class="safety green"></span>
				<strong><?php _e( 'Green', 'trimpress' ); ?></strong> <?php _e( 'indicates a setting is completely safe to implement.', 'trimpress' ); ?>
				
				<br>
				
				<span class="safety blue"></span>
				<strong><?php _e( 'Blue', 'trimpress' ); ?></strong> <?php _e( 'indicates you should proceed with more caution and know the implications.', 'trimpress' ); ?>
			
			</p>

			<hr>

			<button id="btn-all-none" class="button button-secondary">
					<?php _e( 'Select', 'trimpress' ); ?>
			</button>
			
			<select id="opt-all-none">
				<option value="all"><?php _e( 'All', 'trimpress' ); ?></option>
				<option value="none"><?php _e( 'None', 'trimpress' ); ?></option>
			<select>
			
			<form action="options.php" method="post">

				<button type="submit" name="submit1" id="submit1" class="button button-primary">
					<?php _e( 'Save Settings', 'trimpress' ); ?>
				</button>
			
				<?php
				settings_fields( 'trimpress' );
				do_settings_sections( 'trimpress' );
				?>

				<button type="submit" name="submit2" id="submit2" class="button button-primary">
					<?php _e( 'Save Settings', 'trimpress' ); ?>
				</button>

			</form>

		</div>

		<script>

		/**
	 	 * The select all/none functionality.
		 */
		const btn = document.getElementById('btn-all-none');
		const opt = document.getElementById('opt-all-none');
		let checkboxes = document.querySelectorAll('.trimpress-admin input[type="checkbox"]');
		btn.addEventListener('click', function() {
			if (opt.value === 'all') {
				for (let i = 0; i <= checkboxes.length; i += 1) {
					checkboxes[i].checked = true;
				}
			} else {
				for (let i = 0; i <= checkboxes.length; i += 1) {
					checkboxes[i].checked = false;
				}
			}
		});

		</script>

        <?php
	}
}
