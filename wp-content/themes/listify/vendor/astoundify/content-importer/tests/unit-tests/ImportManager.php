<?php
/**
 * Import manager
 *
 * @since 1.0.0
 */
class Test_ImportManager extends WP_Ajax_UnitTestCase {

	public function setUp() {
		parent::setUp();

		wp_set_current_user( $this->factory->user->create( array(
			'role' => 'administrator',
		) ) );
	}

	public function tearDown() {
		wp_set_current_user( 0 );
	}

	protected function make_ajax_call( $action ) {
		try {
			$this->_handleAjax( $action );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}
	}

	public function test_ajax_iteration_returns_json_success_with_valid_data() {
		$_POST = array(
			'iterate_action' => 'import',
			'items' => array(
				array(
					'id' => 'object-1',
					'type' => 'object',
					'data' => array(
						'post_type' => 'post',
						'post_title' => 'Object 1',
						'post_format' => 'aside',
					),
				),
			),
		);

		$this->make_ajax_call( 'astoundify_ci_iterate_item' );

		$response = json_decode( $this->_last_response );

		$this->assertTrue( $response->success );
	}

}
