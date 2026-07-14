<?php
/**
 * Commerce Framework settings page.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Admin;

use AgencyCommerceCore\Settings\Registry;
use AgencyCommerceCore\Settings\Sanitizer;

/**
 * Registers and renders the admin settings interface.
 */
final class SettingsPage {
	/**
	 * Registers admin hooks.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Adds the Commerce Framework admin menu.
	 */
	public function add_menu(): void {
		add_menu_page(
			__( 'Commerce Framework', 'agency-commerce-core' ),
			__( 'Commerce Framework', 'agency-commerce-core' ),
			'manage_options',
			'agency-commerce-framework',
			array( $this, 'render' ),
			'dashicons-store',
			58
		);
	}

	/**
	 * Registers the settings option, sections, and fields.
	 */
	public function register_settings(): void {
		register_setting(
			Registry::OPTION_GROUP,
			Registry::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( Sanitizer::class, 'sanitize' ),
				'default'           => agency_commerce_core_defaults(),
			)
		);

		foreach ( Registry::sections() as $tab => $sections ) {
			$page = $this->page_for_tab( $tab );

			foreach ( $sections as $section_key => $section ) {
				add_settings_section(
					'agency-commerce-core-' . $tab . '-' . $section_key,
					(string) $section['title'],
					'__return_empty_string',
					$page
				);

				foreach ( $section['fields'] as $field_key => $field ) {
					add_settings_field(
						'agency-commerce-core-' . str_replace( '.', '-', $field_key ),
						(string) $field['label'],
						array( $this, 'render_field' ),
						$page,
						'agency-commerce-core-' . $tab . '-' . $section_key,
						array(
							'key'   => $field_key,
							'field' => $field,
						)
					);
				}
			}
		}
	}

	/**
	 * Renders the settings page.
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access Commerce Framework settings.', 'agency-commerce-core' ) );
		}

		$tabs       = Registry::tabs();
		$active_tab = $this->active_tab();
		?>
		<div class="wrap agency-commerce-core-settings">
			<h1><?php esc_html_e( 'Commerce Framework', 'agency-commerce-core' ); ?></h1>
			<?php settings_errors( Registry::OPTION_NAME ); ?>

			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Commerce Framework settings tabs', 'agency-commerce-core' ); ?>">
				<?php foreach ( $tabs as $tab => $label ) : ?>
					<?php
					$tab_url = add_query_arg(
						array(
							'page' => 'agency-commerce-framework',
							'tab'  => $tab,
						),
						admin_url( 'admin.php' )
					);
					?>
					<a
						class="<?php echo esc_attr( 'nav-tab' . ( $active_tab === $tab ? ' nav-tab-active' : '' ) ); ?>"
						href="<?php echo esc_url( $tab_url ); ?>"
					>
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
				<?php settings_fields( Registry::OPTION_GROUP ); ?>
				<input type="hidden" name="_agency_commerce_core_tab" value="<?php echo esc_attr( $active_tab ); ?>" />
				<?php do_settings_sections( $this->page_for_tab( $active_tab ) ); ?>
				<?php submit_button( __( 'Save Settings', 'agency-commerce-core' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders a settings field.
	 *
	 * @param array<string, mixed> $args Field args.
	 */
	public function render_field( array $args ): void {
		$key   = (string) $args['key'];
		$field = is_array( $args['field'] ) ? $args['field'] : array();
		$type  = (string) ( $field['type'] ?? 'text' );
		$value = agency_commerce_core_get_setting( $key );
		$name  = $this->field_name( $key );
		$id    = 'agency-commerce-core-' . sanitize_html_class( str_replace( '.', '-', $key ) );

		switch ( $type ) {
			case 'checkbox':
				$this->render_checkbox( $name, $id, (bool) $value );
				break;

			case 'select':
				$this->render_select( $name, $id, (string) $value, is_array( $field['options'] ?? null ) ? $field['options'] : array() );
				break;

			case 'textarea':
				$this->render_textarea( $name, $id, (string) $value );
				break;

			case 'number':
				$this->render_number( $name, $id, (int) $value, $field );
				break;

			case 'color':
				$this->render_input( $name, $id, 'text', (string) $value, 'regular-text agency-commerce-core-color' );
				break;

			case 'email':
			case 'url':
			case 'font':
			case 'text':
			default:
				$this->render_input( $name, $id, 'font' === $type ? 'text' : $type, (string) $value );
				break;
		}

		if ( ! empty( $field['description'] ) ) {
			printf(
				'<p class="description">%s</p>',
				esc_html( (string) $field['description'] )
			);
		}
	}

	/**
	 * Renders a text-like input.
	 *
	 * @param string $name Input name.
	 * @param string $id Input ID.
	 * @param string $type Input type.
	 * @param string $value Input value.
	 * @param string $input_class Input class.
	 */
	private function render_input( string $name, string $id, string $type, string $value, string $input_class = 'regular-text' ): void {
		printf(
			'<input type="%1$s" class="%2$s" id="%3$s" name="%4$s" value="%5$s" />',
			esc_attr( $type ),
			esc_attr( $input_class ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	/**
	 * Renders a checkbox input.
	 *
	 * @param string $name Input name.
	 * @param string $id Input ID.
	 * @param bool   $value Checked state.
	 */
	private function render_checkbox( string $name, string $id, bool $value ): void {
		printf(
			'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
			esc_attr( $id ),
			esc_attr( $name ),
			checked( $value, true, false ),
			esc_html__( 'Enabled', 'agency-commerce-core' )
		);
	}

	/**
	 * Renders a select input.
	 *
	 * @param string               $name Input name.
	 * @param string               $id Input ID.
	 * @param string               $value Selected value.
	 * @param array<string, mixed> $options Options.
	 */
	private function render_select( string $name, string $id, string $value, array $options ): void {
		printf(
			'<select id="%1$s" name="%2$s">',
			esc_attr( $id ),
			esc_attr( $name )
		);

		foreach ( $options as $option_value => $label ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( (string) $option_value ),
				selected( $value, (string) $option_value, false ),
				esc_html( (string) $label )
			);
		}

		echo '</select>';
	}

	/**
	 * Renders a textarea input.
	 *
	 * @param string $name Input name.
	 * @param string $id Input ID.
	 * @param string $value Input value.
	 */
	private function render_textarea( string $name, string $id, string $value ): void {
		printf(
			'<textarea class="large-text" rows="4" id="%1$s" name="%2$s">%3$s</textarea>',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_textarea( $value )
		);
	}

	/**
	 * Renders a number input.
	 *
	 * @param string               $name Input name.
	 * @param string               $id Input ID.
	 * @param int                  $value Input value.
	 * @param array<string, mixed> $field Field definition.
	 */
	private function render_number( string $name, string $id, int $value, array $field ): void {
		printf(
			'<input type="number" class="small-text" id="%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" />',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( (string) $value ),
			esc_attr( (string) ( $field['min'] ?? 0 ) ),
			esc_attr( (string) ( $field['max'] ?? 999999999 ) )
		);
	}

	/**
	 * Returns the active tab.
	 */
	private function active_tab(): string {
		$tabs = Registry::tabs();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab navigation.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'store_identity';

		return array_key_exists( $tab, $tabs ) ? $tab : 'store_identity';
	}

	/**
	 * Returns a Settings API page key for a tab.
	 *
	 * @param string $tab Tab key.
	 */
	private function page_for_tab( string $tab ): string {
		return 'agency-commerce-core-' . $tab;
	}

	/**
	 * Returns nested option field name for a dot path.
	 *
	 * @param string $key Dot path.
	 */
	private function field_name( string $key ): string {
		$parts = explode( '.', $key );
		$name  = Registry::OPTION_NAME;

		foreach ( $parts as $part ) {
			$name .= '[' . $part . ']';
		}

		return $name;
	}
}
