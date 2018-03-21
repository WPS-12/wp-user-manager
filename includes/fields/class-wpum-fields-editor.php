<?php
/**
 * Handles all the custom fields related functionalities in the backend.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2018, Alessandro Tesoro
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The class that handles the fields editor.
 */
class WPUM_Fields_Editor {

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'admin_menu', [ $this, 'setup_menu_page' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts' ] );
	}

	/**
	 * Add new menu page to the "Users" menu.
	 *
	 * @return void
	 */
	public function setup_menu_page() {
		add_users_page(
			esc_html__( 'WP User Manager Fields Editor' ),
			esc_html__( 'Custom fields' ),
			'manage_options',
			'wpum-custom-fields',
			[ $this, 'display_fields_editor' ]
		);
	}

	/**
	 * Load scripts and styles within the new admin page.
	 *
	 * @return void
	 */
	public function load_scripts() {

		$screen = get_current_screen();

		if( $screen->base == 'users_page_wpum-custom-fields' ) {

			$is_vue_dev = defined( 'WPUM_VUE_DEV' ) && WPUM_VUE_DEV ? true : false;

			if( $is_vue_dev ) {
				wp_register_script( 'wpum-fields-editor', 'http://localhost:8080/fields-editor.js', array(), WPUM_VERSION, true );
			} else {
				wp_die( 'Vue build missing' );
			}

			wp_enqueue_script( 'wpum-fields-editor' );

			$js_variables = [
				'is_addon_installed'  => apply_filters( 'wpum_fields_editor_has_custom_fields_addon', false ),
				'page_title'          => esc_html__( 'WP User Manager Fields Editor' ),
				'labels'              => [
					'table_name'         => esc_html__( 'Group name' ),
					'table_desc'         => esc_html__( 'Group description' ),
					'table_default'      => esc_html__( 'Default' ),
					'table_fields'       => esc_html__( 'Fields' ),
					'table_actions'      => esc_html__( 'Actions' ),
					'table_add_group'    => esc_html__( 'Add new field group' ),
					'table_edit_group'   => esc_html__( 'Edit group settings' ),
					'table_edit_fields'  => esc_html__( 'Customize fields' ),
					'table_delete_group' => esc_html__( 'Delete group' )
				],
				'groups' => $this->get_groups()
			];

			wp_localize_script( 'wpum-fields-editor', 'wpumFieldsEditor', $js_variables );

		}

	}

	/**
	 * Display the fields editor within the admin panel.
	 *
	 * @return void
	 */
	public function display_fields_editor() {
		echo '<div class="wrap"><div id="wpum-fields-editor"></div></div>';
	}

	private function get_groups() {

		$groups            = WPUM()->fields_groups->get_groups();
		$registered_groups = [];

		if( ! empty( $groups ) && is_array( $groups ) ) {
			foreach ( $groups as $group ) {
				$registered_groups[] = [
					'id'          => $group->get_ID(),
					'name'        => $group->get_name(),
					'description' => $group->get_description(),
					'default'     => $group->get_ID() === 1 ? true: false,
					'fields'      => 'Test'
				];
			}
		}

		return $registered_groups;

	}

}

new WPUM_Fields_Editor;
