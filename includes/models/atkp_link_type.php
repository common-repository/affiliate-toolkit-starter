<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


abstract class atkp_link_type {
	const Link = 1;
	const Offer = 2;
	const Cart = 3;
	const Customerreview = 4;
	const Image = 5;
	const WooCommerce = 6;
}