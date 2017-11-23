<?php
/**
 * Single item import
 *
 * @since 1.0.0
 */
interface Astoundify_CI_Import_Item_Interface {
	public function import();
	public function reset();
	public function get_previous_import();
}
