<?php
/**
Plugin Name: JWP Quick Delete
Description: Плагин для быстрого удаления записей, минуя корзину. Будет полезен при тестировании функционала, когда записи постоянно добавляются и удаляются. Теперь, после удаления, не нужно заходить в корзину и очищать её.
Author: Eugene Jokerov
Version: 1.1
Author URI: http://wordpressor.org/
*/

if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


class JWP_Quick_Delete {
	
	protected $action_name = 'quick_delete';
	protected $query_var = 'jwp_qd';
	
	public function __construct() {
		// получаем список публичных типов записей + стандартные
		$post_types = get_post_types( array(
			'public'   => true,
			'_builtin' => true
		) );
		foreach( $post_types as $post_type ) {
			add_filter( "bulk_actions-edit-{$post_type}", array( $this, 'register_bulk_action' ) );
			add_filter( "handle_bulk_actions-edit-{$post_type}", array( $this, 'bulk_action_handler' ), 10, 3 );
		}
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
	}
	
	public function register_bulk_action( $bulk_actions ) {
		$bulk_actions[ $this->action_name ] = __( 'Удалить навсегда', 'jwp_qd');
		return $bulk_actions;
	}
	
	public function bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
		if ( $doaction !== $this->action_name ) {
			return $redirect_to;
		}
		foreach ( $post_ids as $post_id ) {
			$force_delete = true;
			wp_delete_post( $post_id, $force_delete ); // удаляем выбранные записи навсегда
		}
		$redirect_to = add_query_arg( $this->query_var, count( $post_ids ), $redirect_to );
		return $redirect_to;
	}
	
	public function admin_notice() {
		if ( ! empty( $_REQUEST[ $this->query_var ] ) ) {
			printf( '<div id="message" class="updated fade"><p>' . __( 'Записи удалены', 'jwp_qd') . '</p></div>' );
		}
	}
}

// Плагин инициализируется только для админки
if ( is_admin() ) {
	$jwp_qd = new JWP_Quick_Delete;
}
