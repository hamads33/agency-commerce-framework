<?php
/**
 * Commerce Framework settings page.
 *
 * @package AgencyCommerceCore
 */

declare( strict_types=1 );

namespace AgencyCommerceCore\Admin;

use AgencyCommerceCore\Presets\Manager as PresetManager;
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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
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
			<?php $this->render_preset_notice(); ?>

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

			<?php if ( 'presets' === $active_tab ) : ?>
				<?php $this->render_presets_tab(); ?>
			<?php else : ?>
				<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
					<?php settings_fields( Registry::OPTION_GROUP ); ?>
					<input type="hidden" name="_agency_commerce_core_tab" value="<?php echo esc_attr( $active_tab ); ?>" />
					<?php do_settings_sections( $this->page_for_tab( $active_tab ) ); ?>
					<?php submit_button( __( 'Save Settings', 'agency-commerce-core' ) ); ?>
				</form>
			<?php endif; ?>
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

		if ( ! empty( $field['preview_group'] ) ) {
			$this->render_variant_previews( (string) $field['preview_group'], is_array( $field['options'] ?? null ) ? $field['options'] : array(), (string) $value );
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

	/**
	 * Enqueues admin assets for the settings screen.
	 *
	 * @param string $hook Current hook suffix.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( 'toplevel_page_agency-commerce-framework' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'agency-commerce-core-admin',
			plugins_url( 'assets/admin/settings.css', AGENCY_COMMERCE_CORE_FILE ),
			array(),
			AGENCY_COMMERCE_CORE_VERSION
		);
	}

	/**
	 * Renders preset notices from query args.
	 */
	private function render_preset_notice(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin query args.
		$action = isset( $_GET['preset_action'] ) ? sanitize_key( wp_unslash( $_GET['preset_action'] ) ) : '';

		if ( '' === $action ) {
			return;
		}

		$message = '';
		$class   = 'notice notice-info';

		switch ( $action ) {
			case 'applied':
				$class   = 'notice notice-success';
				$message = __( 'Preset applied successfully.', 'agency-commerce-core' );
				break;

			case 'restored':
				$class   = 'notice notice-success';
				$message = __( 'Previous preset restored successfully.', 'agency-commerce-core' );
				break;

			case 'failed':
				$class   = 'notice notice-error';
				$message = __( 'Preset action could not be completed.', 'agency-commerce-core' );
				break;

			case 'invalid':
				$class   = 'notice notice-error';
				$message = __( 'Preset request could not be verified.', 'agency-commerce-core' );
				break;
		}

		if ( '' === $message ) {
			return;
		}

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * Renders the presets tab content.
	 */
	private function render_presets_tab(): void {
		$presets       = PresetManager::all();
		$active_preset = PresetManager::active();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin query args.
		$preview_preset = isset( $_GET['preset'] ) ? sanitize_key( wp_unslash( $_GET['preset'] ) ) : '';
		$preview_preset = array_key_exists( $preview_preset, $presets ) ? $preview_preset : '';
		$snapshot       = PresetManager::snapshot();
		?>
		<div class="agency-commerce-core-presets">
			<div class="agency-commerce-core-presets__grid">
				<?php foreach ( $presets as $slug => $preset ) : ?>
					<?php $this->render_preset_card( $slug, $preset, $active_preset === $slug, $preview_preset === $slug ); ?>
				<?php endforeach; ?>
			</div>

			<?php if ( '' !== $preview_preset ) : ?>
				<?php $this->render_preset_preview_panel( $preview_preset, $presets[ $preview_preset ], $snapshot ); ?>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'Choose Preview on a preset to review the exact framework settings before applying it.', 'agency-commerce-core' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Renders one preset card.
	 *
	 * @param string               $slug Preset slug.
	 * @param array<string, mixed> $preset Preset definition.
	 * @param bool                 $is_active Whether the preset is active.
	 * @param bool                 $is_preview Whether the preset is previewed.
	 */
	private function render_preset_card( string $slug, array $preset, bool $is_active, bool $is_preview ): void {
		$preview_url = add_query_arg(
			array(
				'page'   => 'agency-commerce-framework',
				'tab'    => 'presets',
				'preset' => $slug,
			),
			admin_url( 'admin.php' )
		);
		?>
		<article class="<?php echo esc_attr( 'agency-commerce-core-preset-card' . ( $is_preview ? ' is-preview' : '' ) . ( $is_active ? ' is-active' : '' ) ); ?>">
			<h2><?php echo esc_html( (string) $preset['label'] ); ?></h2>
			<p><?php echo esc_html( (string) $preset['description'] ); ?></p>
			<div class="agency-commerce-core-preset-card__previews">
				<?php $this->render_preset_variant_preview( 'header', (string) ( $preset['settings']['header']['variant'] ?? 'header-default' ) ); ?>
				<?php $this->render_preset_variant_preview( 'footer', (string) ( $preset['settings']['footer']['variant'] ?? 'footer-columns' ) ); ?>
				<?php $this->render_preset_variant_preview( 'product-card', (string) ( $preset['settings']['shop']['card_variant'] ?? 'product-card-classic' ) ); ?>
				<?php $this->render_preset_variant_preview( 'product-layout', (string) ( $preset['settings']['product_page']['layout'] ?? 'product-layout-standard' ) ); ?>
			</div>
			<p class="agency-commerce-core-preset-card__actions">
				<a class="button button-secondary" href="<?php echo esc_url( $preview_url ); ?>"><?php esc_html_e( 'Preview', 'agency-commerce-core' ); ?></a>
				<?php if ( $is_active ) : ?>
					<span class="description"><?php esc_html_e( 'Active preset', 'agency-commerce-core' ); ?></span>
				<?php endif; ?>
			</p>
		</article>
		<?php
	}

	/**
	 * Renders the selected preset preview panel.
	 *
	 * @param string               $slug Preset slug.
	 * @param array<string, mixed> $preset Preset definition.
	 * @param array<string, mixed> $snapshot Previous preset snapshot.
	 */
	private function render_preset_preview_panel( string $slug, array $preset, array $snapshot ): void {
		$apply_nonce   = wp_create_nonce( 'agency_commerce_core_apply_preset_' . $slug );
		$restore_nonce = wp_create_nonce( 'agency_commerce_core_restore_preset' );
		$has_snapshot  = ! empty( $snapshot['settings'] ) && is_array( $snapshot['settings'] );
		?>
		<section class="agency-commerce-core-preset-preview">
			<?php
			/* translators: %s: preset label. */
			echo '<h2>' . esc_html( sprintf( __( '%s preview', 'agency-commerce-core' ), (string) $preset['label'] ) ) . '</h2>';
			?>
			<p><?php echo esc_html( (string) $preset['description'] ); ?></p>
			<div class="agency-commerce-core-preset-preview__details">
				<?php $this->render_preset_summary( $preset ); ?>
			</div>
			<div class="agency-commerce-core-preset-preview__actions">
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="agency_commerce_core_apply_preset" />
					<input type="hidden" name="preset" value="<?php echo esc_attr( $slug ); ?>" />
					<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $apply_nonce ); ?>" />
					<?php submit_button( __( 'Apply preset', 'agency-commerce-core' ), 'primary', 'submit', false ); ?>
				</form>
				<?php if ( $has_snapshot ) : ?>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<input type="hidden" name="action" value="agency_commerce_core_restore_preset" />
						<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $restore_nonce ); ?>" />
						<?php submit_button( __( 'Restore previous preset', 'agency-commerce-core' ), 'secondary', 'submit', false ); ?>
					</form>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Renders a concise preset summary.
	 *
	 * @param array<string, mixed> $preset Preset definition.
	 */
	private function render_preset_summary( array $preset ): void {
		$items = array(
			__( 'Header', 'agency-commerce-core' )         => (string) ( $preset['settings']['header']['variant'] ?? 'header-default' ),
			__( 'Footer', 'agency-commerce-core' )         => (string) ( $preset['settings']['footer']['variant'] ?? 'footer-columns' ),
			__( 'Product card', 'agency-commerce-core' )   => (string) ( $preset['settings']['shop']['card_variant'] ?? 'product-card-classic' ),
			__( 'Product layout', 'agency-commerce-core' ) => (string) ( $preset['settings']['product_page']['layout'] ?? 'product-layout-standard' ),
			__( 'Spacing density', 'agency-commerce-core' ) => (string) ( $preset['settings']['design']['spacing_density'] ?? 'comfortable' ),
			__( 'Shadow level', 'agency-commerce-core' )   => (string) ( $preset['settings']['design']['shadow_level'] ?? 'balanced' ),
		);
		?>
		<ul class="agency-commerce-core-preset-summary">
			<?php foreach ( $items as $label => $value ) : ?>
				<li><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $value ); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Renders a static variant preview thumbnail.
	 *
	 * @param string $group Preview group.
	 * @param string $variant Variant slug.
	 */
	private function render_preset_variant_preview( string $group, string $variant ): void {
		$preview_url = agency_commerce_core_preview_asset_url( $group, $variant );
		$label       = ucfirst( str_replace( '-', ' ', $variant ) );
		?>
		<figure class="agency-commerce-core-variant-preview">
			<img src="<?php echo esc_url( $preview_url ); ?>" alt="<?php echo esc_attr( $label ); ?>" />
			<figcaption><?php echo esc_html( $label ); ?></figcaption>
		</figure>
		<?php
	}

	/**
	 * Renders variant preview thumbnails for a select field.
	 *
	 * @param string               $group Preview group.
	 * @param array<string, mixed> $options Select options.
	 * @param string               $selected Selected option.
	 */
	private function render_variant_previews( string $group, array $options, string $selected ): void {
		if ( empty( $options ) ) {
			return;
		}
		?>
		<div class="agency-commerce-core-variant-previews">
			<?php foreach ( $options as $variant => $label ) : ?>
				<figure class="<?php echo esc_attr( 'agency-commerce-core-variant-preview' . ( $selected === (string) $variant ? ' is-selected' : '' ) ); ?>">
					<img src="<?php echo esc_url( agency_commerce_core_preview_asset_url( $group, (string) $variant ) ); ?>" alt="<?php echo esc_attr( (string) $label ); ?>" />
					<figcaption><?php echo esc_html( (string) $label ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
