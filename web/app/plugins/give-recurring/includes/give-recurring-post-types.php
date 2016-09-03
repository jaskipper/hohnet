<?php
/**
 * Setup Recurring Post Type(s)
 *
 * @description: Currently, CPTs in Recurring are used for logs
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function give_recurring_setup_post_type() {

	register_post_type( 'give_recur_email_log', array(
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => false,
		'query_var'          => false,
		'rewrite'            => false,
		'capability_type'    => 'page',
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => array( 'title', 'editor' ),
		'taxonomies'         => array( 'give_log_type' )
	) );

}
add_action( 'init', 'give_recurring_setup_post_type', 2 );
