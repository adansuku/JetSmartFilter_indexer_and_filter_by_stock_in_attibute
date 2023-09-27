<?php

class JSF_Variation_Attributes_Stock {

	public function __construct() {
		add_filter( 'jet-smart-filters/query/final-query', array( $this, 'modify_query' ) );
	}

	public function modify_query( $query ) {

		if ( ! function_exists( 'WC' ) || ! isset( $query['tax_query'] ) ) {
			return $query;
		}

		foreach ( $query['tax_query'] as $key => $item ) {
		
			if ( isset( $item['taxonomy'] ) && taxonomy_is_product_attribute( $item['taxonomy'] ) ) {

				if ( is_array( $item['terms'] ) ) {
					$terms = implode( ',', $item['terms'] );
				} else {
					$terms = $item['terms'];
				}

				global $wpdb;

				$prefix = $wpdb->prefix;

				$sql = "SELECT product_or_parent_id 
				        FROM {$prefix}wc_product_attributes_lookup 
				        WHERE term_id IN ( {$terms} )
				        AND in_stock = '1'";

				$posts = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'product_or_parent_id' );

				if ( ! empty( $posts ) && isset( $query['post__in'] ) ) {
					$query['post__in'] = array_intersect( $posts, $query['post__in'] );
				} elseif ( ! empty( $posts ) ) {
					$query['post__in'] = $posts;
				}

				if ( empty( $query['post__in'] ) ) {
					$query['post__in'] = array( 0 );
				}

			}
			
		}

		return $query;

	}

}

new JSF_Variation_Attributes_Stock();