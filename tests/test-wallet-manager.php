<?php
/**
 * Class Test_Wallet_Manager
 *
 * @package Sui_User_Wallets
 */

class Test_Wallet_Manager extends WP_UnitTestCase {

    private $manager;
    private $test_user_id;

    public function setUp(): void {
        parent::setUp();

        $this->manager = new SUW_Wallet_Manager();

        // Create test user
        $this->test_user_id = $this->factory->user->create( array(
            'user_login' => 'testuser',
            'user_email' => 'test@example.com',
        ) );

        // Ensure table exists
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';
        $wpdb->query( "TRUNCATE TABLE {$table_name}" );
    }

    public function tearDown(): void {
        // Clean up
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';
        $wpdb->delete( $table_name, array( 'user_id' => $this->test_user_id ) );

        wp_delete_user( $this->test_user_id );

        parent::tearDown();
    }

    /**
     * Test get_user_wallet returns null for non-existent wallet
     */
    public function test_get_user_wallet_nonexistent() {
        $wallet = $this->manager->get_user_wallet( $this->test_user_id );
        $this->assertNull( $wallet );
    }

    /**
     * Test wallet retrieval after creation
     */
    public function test_get_user_wallet_after_creation() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        // Manually insert test wallet
        $test_address = '0x' . str_repeat( 'a', 64 );
        $test_key = 'encrypted_key_data';

        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $this->test_user_id,
                'wallet_address' => $test_address,
                'encrypted_private_key' => $test_key,
            )
        );

        $wallet = $this->manager->get_user_wallet( $this->test_user_id );

        $this->assertNotNull( $wallet );
        $this->assertEquals( $test_address, $wallet->wallet_address );
        $this->assertEquals( $this->test_user_id, $wallet->user_id );
    }

    /**
     * Test get_all_wallets
     */
    public function test_get_all_wallets() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        // Create multiple test wallets
        for ( $i = 0; $i < 3; $i++ ) {
            $user_id = $this->factory->user->create();
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'wallet_address' => '0x' . str_repeat( chr( 97 + $i ), 64 ),
                    'encrypted_private_key' => 'key_' . $i,
                )
            );
        }

        $wallets = $this->manager->get_all_wallets();

        $this->assertIsArray( $wallets );
        $this->assertGreaterThanOrEqual( 3, count( $wallets ) );
    }

    /**
     * Test database table exists
     */
    public function test_table_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        $result = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );

        $this->assertEquals( $table_name, $result );
    }
}
