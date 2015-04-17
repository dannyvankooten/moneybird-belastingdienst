<?php

/**
 * @param array $data
 *
 * @return string
 */
function generate_row_html( array $data ) {

	$html = (string) file_get_contents( __DIR__ . '/template.html' );
	$regex = '/\{\{(\w+)\}\}/';

	$html = preg_replace_callback( $regex, function($matches) use($data) {
		$variable_name = strtolower( $matches[1] );
		$replacement = ( isset( $data[ $variable_name ] ) ) ? $data[ $variable_name ] : '';
		return $replacement;
	}, $html );

	return $html;
}
