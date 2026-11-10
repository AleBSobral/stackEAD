<?php
namespace StackEAD\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WhiteLabelSettings - Enterprise Institutional Customization for StackEAD Core.
 *
 * Manages institutional branding, logo assets, brand colors, contact channels,
 * social networks, homepage display mode, and dynamically injects CSS tokens and favicons.
 */
class WhiteLabelSettings {

	public const OPTION_NAME = 'stackead_whitelabel_settings';

	/**
	 * Initialize White Label hooks.
	 */
	public static function init(): void {
		add_action( 'wp_head', array( __CLASS__, 'inject_brand_tokens' ), 1 );
		add_action( 'wp_head', array( __CLASS__, 'inject_favicon' ), 2 );
	}

	/**
	 * Get all White Label settings with sensible defaults.
	 *
	 * @return array
	 */
	public static function get_settings(): array {
		$saved = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$plugin_url = defined( 'STACKEAD_URL' ) ? STACKEAD_URL : plugin_dir_url( dirname( __DIR__ ) );

		$defaults = array(
			'institution_name'    => 'ArtInnova',
			'institution_tagline' => 'Residência Internacional de Artes Midiáticas · Brasil + França',
			'logo_url'            => $plugin_url . 'assets/images/artinnova-logo-white.png',
			'logo_dark_url'       => $plugin_url . 'assets/images/artinnova-logo-black.png',
			'logo_color_url'      => $plugin_url . 'assets/images/artinnova-logo-color.png',
			'favicon_url'         => $plugin_url . 'assets/images/artinnova-logo-color.png',
			'primary_color'       => '#2563eb',
			'secondary_color'     => '#06b6d4',
			'support_email'       => 'contact@artinnovaresidency.com',
			'contact_phone'       => '(83) 3333-0000',
			'whatsapp_number'     => '5583999999999',
			'physical_address'    => 'Campina Grande, PB · Brasil',
			'instagram_url'       => 'https://instagram.com/artinnova',
			'youtube_url'         => '',
			'linkedin_url'        => '',
			'facebook_url'        => '',
			'homepage_mode'       => 'course_landing', // 'course_landing' or 'institutional_portal'
			'featured_course_id'  => 0,
		);

		return wp_parse_args( $saved, $defaults );
	}

	/**
	 * Save and sanitize White Label settings.
	 *
	 * @param array $input
	 * @return array Sanitized settings
	 */
	public static function save_settings( array $input ): array {
		$current = self::get_settings();

		$sanitized = array(
			'institution_name'    => sanitize_text_field( $input['institution_name'] ?? $current['institution_name'] ),
			'institution_tagline' => sanitize_text_field( $input['institution_tagline'] ?? $current['institution_tagline'] ),
			'logo_url'            => esc_url_raw( $input['logo_url'] ?? $current['logo_url'] ),
			'logo_dark_url'       => esc_url_raw( $input['logo_dark_url'] ?? $current['logo_dark_url'] ),
			'favicon_url'         => esc_url_raw( $input['favicon_url'] ?? $current['favicon_url'] ),
			'primary_color'       => sanitize_hex_color( $input['primary_color'] ?? $current['primary_color'] ) ?: '#4f46e5',
			'secondary_color'     => sanitize_hex_color( $input['secondary_color'] ?? $current['secondary_color'] ) ?: '#06b6d4',
			'support_email'       => sanitize_email( $input['support_email'] ?? $current['support_email'] ),
			'contact_phone'       => sanitize_text_field( $input['contact_phone'] ?? $current['contact_phone'] ),
			'whatsapp_number'     => preg_replace( '/[^0-9]/', '', (string) ( $input['whatsapp_number'] ?? $current['whatsapp_number'] ) ),
			'physical_address'    => sanitize_text_field( $input['physical_address'] ?? $current['physical_address'] ),
			'instagram_url'       => esc_url_raw( $input['instagram_url'] ?? $current['instagram_url'] ),
			'youtube_url'         => esc_url_raw( $input['youtube_url'] ?? $current['youtube_url'] ),
			'linkedin_url'        => esc_url_raw( $input['linkedin_url'] ?? $current['linkedin_url'] ),
			'facebook_url'        => esc_url_raw( $input['facebook_url'] ?? $current['facebook_url'] ),
			'homepage_mode'       => in_array( $input['homepage_mode'] ?? '', array( 'course_landing', 'institutional_portal' ), true ) ? $input['homepage_mode'] : 'course_landing',
			'featured_course_id'  => absint( $input['featured_course_id'] ?? $current['featured_course_id'] ),
		);

		update_option( self::OPTION_NAME, $sanitized );

		return $sanitized;
	}

	/**
	 * Injects CSS variables into :root for dynamic branding.
	 */
	public static function inject_brand_tokens(): void {
		$settings = self::get_settings();
		$primary = $settings['primary_color'];
		$secondary = $settings['secondary_color'];

		// Calculate text contrast (black or white) based on relative luminance
		$primary_text = self::get_contrast_color( $primary );
		$secondary_text = self::get_contrast_color( $secondary );

		echo "\n<!-- StackEAD White Label Brand Tokens -->\n";
		echo "<style id=\"stackead-brand-tokens\">\n";
		echo ":root {\n";
		echo "  --brand-primary: {$primary};\n";
		echo "  --brand-primary-text: {$primary_text};\n";
		echo "  --brand-secondary: {$secondary};\n";
		echo "  --brand-secondary-text: {$secondary_text};\n";
		echo "}\n";
		echo "</style>\n";
	}

	/**
	 * Injects customized favicon if defined.
	 */
	public static function inject_favicon(): void {
		$settings = self::get_settings();
		if ( ! empty( $settings['favicon_url'] ) ) {
			echo '<link rel="icon" href="' . esc_url( $settings['favicon_url'] ) . '" sizes="32x32" />' . "\n";
		}
	}

	/**
	 * Determines whether white or black text should be used based on background color luminance.
	 *
	 * @param string $hex
	 * @return string '#ffffff' or '#0f172a'
	 */
	public static function get_contrast_color( string $hex ): string {
		$hex = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( strlen( $hex ) !== 6 ) {
			return '#ffffff';
		}

		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		// Perceived brightness formula
		$yiq = ( ( $r * 299 ) + ( $g * 587 ) + ( $b * 114 ) ) / 1000;

		return ( $yiq >= 150 ) ? '#0f172a' : '#ffffff';
	}
}
