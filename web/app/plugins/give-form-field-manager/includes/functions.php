<?php
/**
 * Returns an array of allowed extensions to be used with upload form field
 * @return array an array of allowed extensions
 */
function ffm_allowed_extensions() {
	$extensions = array(
		'images' => array(
			'ext'   => 'jpg,jpeg,gif,png,bmp',
			'label' => __( 'Images', 'give-ffm' )
		),
		'audio'  => array(
			'ext'   => 'mp3,wav,ogg,wma,mka,m4a,ra,mid,midi',
			'label' => __( 'Audio', 'give-ffm' )
		),
		'video'  => array(
			'ext'   => 'avi,divx,flv,mov,ogv,mkv,mp4,m4v,divx,mpg,mpeg,mpe',
			'label' => __( 'Videos', 'give-ffm' )
		),
		'pdf'    => array(
			'ext'   => 'pdf',
			'label' => __( 'PDF', 'give-ffm' )
		),
		'office' => array(
			'ext'   => 'doc,ppt,pps,xls,mdb,docx,xlsx,pptx,odt,odp,ods,odg,odc,odb,odf,rtf,txt',
			'label' => __( 'Office Documents', 'give-ffm' )
		),
		'zip'    => array(
			'ext'   => 'zip,gz,gzip,rar,7z',
			'label' => __( 'Zip Archives' )
		),
		'exe'    => array(
			'ext'   => 'exe',
			'label' => __( 'Executable Files', 'give-ffm' )
		),
		'csv'    => array(
			'ext'   => 'csv',
			'label' => __( 'CSV', 'give-ffm' )
		)
	);

	return apply_filters( 'ffm_allowed_extensions', $extensions );
}

/**
 * Associate attachment to a transaction post
 *
 * @since 1.0
 *
 * @param $attachment_id
 * @param $post_id
 */
function ffm_associate_attachment( $attachment_id, $post_id ) {
	wp_update_post( array(
		'ID'          => $attachment_id,
		'post_parent' => $post_id
	) );
}
