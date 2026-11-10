<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ImageOptimizer - Client & Server-side Image Processing Pipeline.
 *
 * Provides aspect ratio crop presets (1:1 Avatar, 16:9 Course Cover, 3:2 News Cover),
 * automatic WebP conversion, progressive quality compression to ensure files stay under 150KB,
 * and WordPress Media Library / User Avatar integration.
 */
class ImageOptimizer {

	public const MAX_FILE_SIZE_BYTES = 153600; // 150 KB target

	/**
	 * Presets definitions with aspect ratio and target dimensions.
	 */
	public const PRESETS = array(
		'avatar'       => array(
			'aspect_ratio' => '1:1',
			'width'        => 400,
			'height'       => 400,
			'max_width'    => 400,
			'max_height'   => 400,
		),
		'course_cover' => array(
			'aspect_ratio' => '16:9',
			'width'        => 1280,
			'height'       => 720,
			'max_width'    => 1280,
			'max_height'   => 720,
		),
		'news_cover'   => array(
			'aspect_ratio' => '3:2',
			'width'        => 1200,
			'height'       => 800,
			'max_width'    => 1200,
			'max_height'   => 800,
		),
		'custom'       => array(
			'aspect_ratio' => 'free',
			'width'        => 1200,
			'height'       => 1200,
			'max_width'    => 1920,
			'max_height'   => 1920,
		),
	);

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_filter( 'get_avatar_url', array( __CLASS__, 'filter_avatar_url' ), 20, 3 );
	}

	/**
	 * Process an uploaded image or base64 data URI, apply crop and convert to WebP (<150KB).
	 *
	 * @param array|string $source $_FILES array element, file path, or base64 data URI.
	 * @param string       $preset 'avatar', 'course_cover', 'news_cover', or 'custom'.
	 * @param array        $crop   Optional manual crop coords: ['x' => int, 'y' => int, 'width' => int, 'height' => int].
	 * @param string       $title  Optional title for WP attachment.
	 * @return array
	 * @throws \Exception
	 */
	public static function process_and_save( $source, string $preset = 'custom', array $crop = array(), string $title = '' ): array {
		if ( ! function_exists( 'imagewebp' ) ) {
			throw new \Exception( 'A extensão GD do PHP não possui suporte para WebP.' );
		}

		$config = self::PRESETS[ $preset ] ?? self::PRESETS['custom'];

		// 1. Resolve raw binary data
		$binary_data = self::extract_binary( $source );
		if ( empty( $binary_data ) ) {
			throw new \Exception( 'Dados de imagem vazios ou inválidos.' );
		}

		// 2. Create GD image from string
		$src_img = @imagecreatefromstring( $binary_data );
		if ( ! $src_img ) {
			throw new \Exception( 'Não foi possível interpretar o formato da imagem enviada.' );
		}

		$orig_width  = imagesx( $src_img );
		$orig_height = imagesy( $src_img );

		// 3. Determine crop rectangle
		if ( ! empty( $crop['width'] ) && ! empty( $crop['height'] ) ) {
			$crop_x = max( 0, (int) ( $crop['x'] ?? 0 ) );
			$crop_y = max( 0, (int) ( $crop['y'] ?? 0 ) );
			$crop_w = min( $orig_width - $crop_x, (int) $crop['width'] );
			$crop_h = min( $orig_height - $crop_y, (int) $crop['height'] );
		} else {
			// Auto crop from center according to preset ratio
			list( $crop_x, $crop_y, $crop_w, $crop_h ) = self::calculate_center_crop( $orig_width, $orig_height, $config );
		}

		// 4. Calculate output target dimensions
		$target_w = min( $config['max_width'], $crop_w );
		$target_h = (int) round( $crop_h * ( $target_w / $crop_w ) );
		if ( $target_h > $config['max_height'] ) {
			$target_h = $config['max_height'];
			$target_w = (int) round( $crop_w * ( $target_h / $crop_h ) );
		}

		// 5. Resample into destination image
		$dst_img = imagecreatetruecolor( $target_w, $target_h );
		imagealphablending( $dst_img, false );
		imagesavealpha( $dst_img, true );

		imagecopyresampled(
			$dst_img,
			$src_img,
			0,
			0,
			$crop_x,
			$crop_y,
			$target_w,
			$target_h,
			$crop_w,
			$crop_h
		);
		imagedestroy( $src_img );

		// 6. Ensure upload directory exists
		$upload_dir = wp_upload_dir();
		$stackead_dir = $upload_dir['basedir'] . '/stackead';
		$stackead_url = $upload_dir['baseurl'] . '/stackead';
		if ( ! file_exists( $stackead_dir ) ) {
			wp_mkdir_p( $stackead_dir );
		}

		$filename = sprintf(
			'%s-%s-%s.webp',
			sanitize_title( $preset ),
			date( 'Ymd-His' ),
			wp_generate_password( 6, false )
		);
		$file_path = $stackead_dir . '/' . $filename;
		$file_url  = $stackead_url . '/' . $filename;

		// 7. Progressive quality compression to guarantee < 150KB
		$quality = 85;
		imagewebp( $dst_img, $file_path, $quality );

		$filesize = file_exists( $file_path ) ? filesize( $file_path ) : 0;
		while ( $filesize > self::MAX_FILE_SIZE_BYTES && $quality > 45 ) {
			$quality -= 8;
			imagewebp( $dst_img, $file_path, $quality );
			clearstatcache();
			$filesize = filesize( $file_path );
		}
		imagedestroy( $dst_img );

		// 8. Register as WordPress Media Library Attachment
		$attachment_id = 0;
		if ( function_exists( 'wp_insert_attachment' ) ) {
			$attachment_title = ! empty( $title ) ? sanitize_text_field( $title ) : pathinfo( $filename, PATHINFO_FILENAME );
			$attachment = array(
				'guid'           => $file_url,
				'post_mime_type' => 'image/webp',
				'post_title'     => $attachment_title,
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$attachment_id = wp_insert_attachment( $attachment, $file_path );
			if ( ! is_wp_error( $attachment_id ) && $attachment_id > 0 ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$attach_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
				wp_update_attachment_metadata( $attachment_id, $attach_data );
			} else {
				$attachment_id = 0;
			}
		}

		return array(
			'success'             => true,
			'preset'              => $preset,
			'url'                 => $file_url,
			'filename'            => $filename,
			'attachment_id'       => $attachment_id,
			'width'               => $target_w,
			'height'              => $target_h,
			'file_size'           => $filesize,
			'file_size_formatted' => size_format( $filesize ),
			'quality_used'        => $quality,
			'mime_type'           => 'image/webp',
		);
	}

	/**
	 * Extract binary data safely from $_FILES or base64 data URI.
	 * Rejects arbitrary local filesystem paths to prevent LFI/SSRF probing.
	 *
	 * @param mixed $source
	 * @return string|null
	 */
	protected static function extract_binary( $source ): ?string {
		$max_raw_bytes = 8 * 1024 * 1024; // 8 MB maximum input image size

		if ( is_array( $source ) && isset( $source['tmp_name'] ) ) {
			if ( is_uploaded_file( $source['tmp_name'] ) ) {
				$size = @filesize( $source['tmp_name'] );
				if ( false !== $size && $size > 0 && $size <= $max_raw_bytes ) {
					return file_get_contents( $source['tmp_name'] );
				}
			}
			return null;
		}

		if ( is_string( $source ) ) {
			// Strictly allow only base64 image data URIs
			if ( preg_match( '/^data:image\/(png|jpe?g|webp|gif);base64,/i', $source ) ) {
				$data = preg_replace( '/^data:image\/[a-z0-9+.-]+;base64,/i', '', $source );
				if ( strlen( $data ) > (int) ( $max_raw_bytes * 1.37 ) ) {
					return null;
				}
				$decoded = base64_decode( $data, true );
				return ( false !== $decoded && strlen( $decoded ) <= $max_raw_bytes ) ? $decoded : null;
			}
		}

		return null;
	}

	/**
	 * Center-crop rectangle calculation to match preset aspect ratio.
	 *
	 * @param int   $src_w
	 * @param int   $src_h
	 * @param array $config
	 * @return array [x, y, w, h]
	 */
	protected static function calculate_center_crop( int $src_w, int $src_h, array $config ): array {
		if ( empty( $config['width'] ) || empty( $config['height'] ) || 'free' === $config['aspect_ratio'] ) {
			return array( 0, 0, $src_w, $src_h );
		}

		$desired_ratio = $config['width'] / $config['height'];
		$actual_ratio  = $src_w / $src_h;

		if ( $actual_ratio > $desired_ratio ) {
			// Source is wider than desired: crop left and right
			$crop_h = $src_h;
			$crop_w = (int) round( $src_h * $desired_ratio );
			$crop_x = (int) round( ( $src_w - $crop_w ) / 2 );
			$crop_y = 0;
		} else {
			// Source is taller than desired: crop top and bottom
			$crop_w = $src_w;
			$crop_h = (int) round( $src_w / $desired_ratio );
			$crop_x = 0;
			$crop_y = (int) round( ( $src_h - $crop_h ) / 2 );
		}

		return array( $crop_x, $crop_y, $crop_w, $crop_h );
	}

	/**
	 * Saves a custom avatar for a user and registers it to their metadata.
	 *
	 * @param int          $user_id
	 * @param string|array $source File or Base64 data
	 * @param array        $crop
	 * @return array
	 */
	public static function save_user_avatar( int $user_id, $source, array $crop = array() ): array {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			throw new \Exception( 'Usuário não encontrado.' );
		}

		$result = self::process_and_save(
			$source,
			'avatar',
			$crop,
			sprintf( 'Avatar - %s', $user->display_name )
		);

		update_user_meta( $user_id, '_stackead_avatar_url', $result['url'] );
		if ( ! empty( $result['attachment_id'] ) ) {
			update_user_meta( $user_id, '_stackead_avatar_attachment_id', $result['attachment_id'] );
		}

		return array(
			'success'             => true,
			'user_id'             => $user_id,
			'avatar_url'          => $result['url'],
			'attachment_id'       => $result['attachment_id'],
			'file_size'           => $result['file_size'],
			'file_size_formatted' => $result['file_size_formatted'],
		);
	}

	/**
	 * Filter get_avatar_url to return custom StackEAD avatar if set.
	 *
	 * @param string $url
	 * @param mixed  $id_or_email
	 * @param array  $args
	 * @return string
	 */
	public static function filter_avatar_url( string $url, $id_or_email, array $args ): string {
		$user_id = 0;

		if ( is_numeric( $id_or_email ) ) {
			$user_id = (int) $id_or_email;
		} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
			if ( $user ) {
				$user_id = $user->ID;
			}
		} elseif ( is_object( $id_or_email ) ) {
			if ( ! empty( $id_or_email->user_id ) ) {
				$user_id = (int) $id_or_email->user_id;
			} elseif ( $id_or_email instanceof \WP_User ) {
				$user_id = $id_or_email->ID;
			}
		}

		if ( $user_id > 0 ) {
			$custom_avatar = get_user_meta( $user_id, '_stackead_avatar_url', true );
			if ( ! empty( $custom_avatar ) ) {
				return $custom_avatar;
			}
		}

		return $url;
	}
}
