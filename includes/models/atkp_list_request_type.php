<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


abstract class atkp_list_request_type {
	const TopSellers = 'TopSellers';
	const NewReleases = 'NewReleases';
	const ExtendedSearch = 'ExtendedSearch';
	const Search = 'Search';
}