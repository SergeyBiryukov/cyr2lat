<?php
/**
 * Test_Post_Conversion_Process class file
 *
 * @package cyr-to-lat
 * @group   process
 */

namespace Cyr_To_Lat;

use Mockery;
use ReflectionException;
use wpdb;

/**
 * Class Test_Post_Conversion_Process
 *
 * @group process
 */
class Test_Post_Conversion_Process extends Cyr_To_Lat_TestCase {

	/**
	 * End test
	 */
	public function tearDown() {
		unset( $GLOBALS['wpdb'] );
		parent::tearDown();
	}

	/**
	 * Test task()
	 *
	 * @param string $post_name           Post name.
	 * @param string $transliterated_name Sanitized post name.
	 *
	 * @dataProvider dp_test_task
	 */
	public function test_task( $post_name, $transliterated_name ) {
		global $wpdb;

		$post = (object) [
			'ID'        => 5,
			'post_name' => $post_name,
		];

		$main = Mockery::mock( Main::class );
		$main->shouldReceive( 'transliterate' )->with( $post_name )->andReturn( $transliterated_name );

		if ( $transliterated_name !== $post->post_name ) {
			\WP_Mock::userFunction(
				'update_post_meta',
				[
					'args'  => [ $post->ID, '_wp_old_slug', $post->post_name ],
					'times' => 1,
				]
			);
			$wpdb        = Mockery::mock( wpdb::class );
			$wpdb->posts = 'wp_posts';
			$wpdb->shouldReceive( 'update' )->once()->
			with( $wpdb->posts, [ 'post_name' => $transliterated_name ], [ 'ID' => $post->ID ] );
		}

		\WP_Mock::userFunction(
			'get_locale',
			[ 'return' => 'ru_RU' ]
		);

		$subject = Mockery::mock( Post_Conversion_Process::class, [ $main ] )->makePartial()->
		shouldAllowMockingProtectedMethods();

		\WP_Mock::expectFilterAdded(
			'locale',
			[ $subject, 'filter_post_locale' ]
		);

		\WP_Mock::userFunction(
			'remove_filter',
			[
				'args'  => [ 'locale', [ $subject, 'filter_post_locale' ] ],
				'times' => 1,
			]
		);

		if ( $transliterated_name !== $post->post_name ) {
			$subject
				->shouldReceive( 'log' )
				->with( 'Post slug converted: ' . $post->post_name . ' => ' . $transliterated_name )
				->once();
		}

		$this->assertFalse( $subject->task( $post ) );
	}

	/**
	 * Data provider for test_task()
	 */
	public function dp_test_task() {
		return [
			[ 'post_name', 'post_name' ],
			[ 'post_name', 'transliterated_name' ],
		];
	}

	/**
	 * Test complete()
	 */
	public function test_complete() {
		$subject = Mockery::mock( Post_Conversion_Process::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$subject->shouldReceive( 'log' )->with( 'Post slugs conversion completed.' )->once();

		\WP_Mock::userFunction(
			'wp_next_scheduled',
			[
				'return' => null,
				'times'  => 1,
			]
		);

		\WP_Mock::userFunction(
			'set_site_transient',
			[
				'times' => 1,
			]
		);

		$subject->complete();
	}

	/**
	 * Tests filter_post_locale()
	 *
	 * @param array  $wpml_post_language_details Post language details.
	 * @param string $locale                     Site locale.
	 * @param string $expected                   Expected result.
	 *
	 * @dataProvider dp_test_filter_post_locale
	 * @throws ReflectionException Reflection exception.
	 */
	public function test_filter_post_locale( $wpml_post_language_details, $locale, $expected ) {
		$post = (object) [
			'ID' => 5,
		];

		\WP_Mock::onFilter( 'wpml_post_language_details' )->with( false, $post->ID )->reply( $wpml_post_language_details );

		\WP_Mock::userFunction(
			'get_locale',
			[
				'return' => $locale,
			]
		);

		$main    = Mockery::mock( Main::class );
		$subject = new Post_Conversion_Process( $main );
		$this->set_protected_property( $subject, 'post', $post );
		$this->assertSame( $expected, $subject->filter_post_locale() );
	}

	/**
	 * Data provider for test_filter_post_locale()
	 *
	 * @return array
	 */
	public function dp_test_filter_post_locale() {
		return [
			[ null, 'ru_RU', 'ru_RU' ],
			[ [], 'ru_RU', 'ru_RU' ],
			[ [ 'some' => 'uk' ], 'ru_RU', 'ru_RU' ],
			[ [ 'locale' => 'uk' ], 'ru_RU', 'uk' ],
		];
	}
}
