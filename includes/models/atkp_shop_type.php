<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class atkp_shop_type {
	/**
	 *Amazon/CSV/Tradedoubler -> Ein Request pro Shop
	 */
	public const SINGLE_SHOP = 'single_shop';
	/**
	 *Yadore/Billiger/Geizhals -> Mehrere Subshops -> Pro Parent ein Request
	 */
	public const MULTI_SHOPS = 'multi_shops';
	/**
	 *AWIN -> Mehrere Subshops -> Pro Subshop ein Request
	 */
	public const SUB_SHOPS = 'sub_shops';

	/**
	 *Wenn es sich um einen Subshop handelt, ist es dieser typ
	 */
	public const CHILD_SHOP = 'child_shop';
}