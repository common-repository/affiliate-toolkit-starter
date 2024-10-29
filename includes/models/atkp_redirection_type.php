<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_redirection_type {
	/**
	 * Disabled
	 */
	public const DISABLED = 0;
	/**
	 * internal redirection
	 */
	public const INTERNAL_REDIRECTION = 2;
	/**
	 * bit.ly shortener
	 */
	public const BIT_LY = 3;
	/**
	 * goo.gl shortener
	 * OBSOLET
	 */
	public const GOO_GL = 4;
	/**
	 * internal redirection name
	 */
	public const INTERNAL_REDIRECTION_NAME = 5;
}