<?php
/*
    Plugin Name: Calculadora Financiera
    Author: Oliver Cera
*/

include_once('acf.php');
include_once('acf-free.php');

function create_dato_anual() {

	register_post_type( 'dato_anual',
	// CPT Options
		array(
			'labels' => array(
				'name' => __( 'Dato Anual' ),
				'singular_name' => __( 'Dato Anual' )
			),
            'supports' => array( 'title' ),
            'exclude_from_search' => true,
            'show_ui'             => true,
	        'show_in_menu'        => true,
	        'show_in_nav_menus'   => false,
	        'show_in_admin_bar'   => true,
	        'menu_position'       => 5,
			'public' => false,
			'has_archive' => false,
			'rewrite' => array('slug' => 'dato_anual'),
		)
	);
}
// Hooking up our function to theme setup
add_action( 'init', 'create_dato_anual' );

add_shortcode('calculadora-financiera', 'fcalc_shortcode');

function fcalc_shortcode(){
    include 'shortcode.php';
}

add_action( 'wp_ajax_get_dato_anual', 'get_dato_anual' );
add_action( 'wp_ajax_nopriv_get_dato_anual', 'get_dato_anual' );

function get_dato_anual(){

	if(isset($_POST['tiempo'])){
		$tiempo = (int) $_POST['tiempo'];
		$ano_buscar = date('Y') - $tiempo;
		$ano_actual = date('Y');
		$ano_rango = [];

		for($i=$ano_buscar; $i<= $ano_actual; $i++){
			$ano_rango[] = $i;
		}

		$result = query_posts(
			array(
				'post_type' => 'dato_anual',
				'title' => $ano_buscar,
				'posts_per_page' => 1
			)
		);

		if( count( $result ) > 0 ){
			$post = $result[0];
			$fields = get_fields($post->ID);
			
			$result_d = query_posts(
				array(
					'post_type' => 'dato_anual',
					'title__in' => $ano_rango,
					'posts_per_page' => -1
				)
			);

			$dividendos = [];

			if( count( $result ) > 0 ){
				foreach( $result_d as $post ){
					$fields = get_fields($post->ID);
					$dividendos[$post->post_title] = $fields['multiplicador_dividendo']; 
				}
			}

			wp_send_json(
				[
					'exitoso' => true,
					'ano_actual' => $ano_actual,
					'ano_buscar' => $ano_buscar,
					'valores' => $fields,
					'dividendos' => $dividendos
				]
			);

		}else{
			wp_send_json(  
				[
					'exitoso' => false,
					'mensaje' => 'No tenemos data suficiente'
				]
			);
		}
	}
}