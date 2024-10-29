<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


abstract class atkp_list_source_type {
	const BestSeller = 10;
	const NewReleases = 11;
	const Search = 20;
	const ExtendedSearch = 30;
}