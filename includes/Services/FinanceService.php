<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles financial plans, student orders, automated enrollment on payment, and revenue stats.
 */
class FinanceService {

	/**
	 * Create a new order.
	 *
	 * @param int         $user_id
	 * @param string      $plan_key
	 * @param float       $amount
	 * @param int|null    $cohort_id
	 * @param string      $payment_method pix|credit_card|boleto
	 * @param string|null $transaction_id
	 * @return int Order ID
	 */
	public static function create_order(
		int $user_id,
		string $plan_key,
		float $amount,
		?int $cohort_id = null,
		string $payment_method = 'pix',
		?string $transaction_id = null
	): int {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_orders';
		$now   = current_time( 'mysql' );

		// 30 days default expiration for monthly subscription
		$expires_at = date( 'Y-m-d H:i:s', strtotime( '+30 days' ) );

		$wpdb->insert(
			$table,
			array(
				'user_id'        => $user_id,
				'cohort_id'      => $cohort_id,
				'plan_key'       => sanitize_text_field( $plan_key ),
				'amount'         => $amount,
				'currency'       => 'BRL',
				'status'         => 'pending',
				'payment_method' => sanitize_text_field( $payment_method ),
				'transaction_id' => $transaction_id ? sanitize_text_field( $transaction_id ) : 'TX-' . strtoupper( wp_generate_password( 10, false ) ),
				'expires_at'     => $expires_at,
				'created_at'     => $now,
				'updated_at'     => $now,
			),
			array( '%d', '%d', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Mark order as completed and automatically enroll student.
	 *
	 * @param int $order_id
	 * @return bool
	 */
	public static function complete_order( int $order_id ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_orders';
		$order = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $order_id )
		);

		if ( ! $order ) {
			return false;
		}

		$now = current_time( 'mysql' );

		$wpdb->update(
			$table,
			array(
				'status'     => 'completed',
				'updated_at' => $now,
			),
			array( 'id' => $order_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		// If tied to a specific cohort, auto-enroll student!
		if ( ! empty( $order->cohort_id ) ) {
			$path_id = (int) get_post_meta( $order->cohort_id, '_stackead_path_id', true );
			EnrollmentService::enroll( (int) $order->user_id, (int) $order->cohort_id, $path_id, 'active' );
		}

		do_action( 'stackead_order_completed', $order_id, (int) $order->user_id, (float) $order->amount );

		return true;
	}

	/**
	 * Get financial metrics summary for admin/coordinator.
	 *
	 * @return array ['total_revenue' => float, 'orders_count' => int, 'active_subscribers' => int, 'recent_orders' => array]
	 */
	public static function get_financial_summary(): array {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_orders';

		$total_revenue = (float) $wpdb->get_var(
			"SELECT SUM(amount) FROM {$table} WHERE status = 'completed'"
		);

		$orders_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table} WHERE status = 'completed'"
		);

		$active_subscribers = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT user_id) FROM {$table} WHERE status = 'completed' AND (expires_at IS NULL OR expires_at > NOW())"
		);

		$recent_orders = $wpdb->get_results(
			"SELECT o.*, u.display_name, u.user_email
			FROM {$table} o
			INNER JOIN {$wpdb->users} u ON o.user_id = u.ID
			ORDER BY o.created_at DESC
			LIMIT 15"
		);

		return array(
			'total_revenue'      => $total_revenue > 0 ? $total_revenue : 0.0,
			'orders_count'       => $orders_count,
			'active_subscribers' => $active_subscribers,
			'recent_orders'      => ! empty( $recent_orders ) ? $recent_orders : array(),
		);
	}

	/**
	 * Process immediate payment confirmation and auto-enroll student.
	 *
	 * @param int         $user_id
	 * @param int         $cohort_id
	 * @param float       $amount
	 * @param string      $payment_method (mercadopago, paypal, stripe, pix)
	 * @param string|null $transaction_id
	 * @return array
	 */
	public static function process_instant_enrollment(
		int $user_id,
		int $cohort_id,
		float $amount,
		string $payment_method = 'mercadopago',
		?string $transaction_id = null
	): array {
		$order_id = self::create_order(
			$user_id,
			'full_enrollment',
			$amount,
			$cohort_id,
			$payment_method,
			$transaction_id ?: 'INSTANT-' . strtoupper( wp_generate_password( 8, false ) )
		);

		$success = self::complete_order( $order_id );

		return array(
			'success'      => $success,
			'order_id'     => $order_id,
			'cohort_id'    => $cohort_id,
			'user_id'      => $user_id,
			'redirect_url' => home_url( '/plataforma' ),
			'message'      => 'Pagamento confirmado com sucesso! Sua vaga e acesso à plataforma foram liberados.',
		);
	}
}
