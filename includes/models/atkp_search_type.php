<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


abstract class atkp_search_type {
	const ASIN = 'asin';
	const EAN = 'ean';
	const Title = 'title';
	const Articlenumber = 'articlenumber';
}