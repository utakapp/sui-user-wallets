<?php
/**
 * Class Test_Wallet_Crypto
 *
 * @package Sui_User_Wallets
 */

class Test_Wallet_Crypto extends WP_UnitTestCase {

    private $crypto;

    public function setUp(): void {
        parent::setUp();
        $this->crypto = new SUW_Wallet_Crypto();
    }

    /**
     * Test encryption and decryption
     */
    public function test_encrypt_decrypt() {
        $original = 'suiprivkey1qz...test...';

        $encrypted = $this->crypto->encrypt( $original );
        $this->assertNotEquals( $original, $encrypted );
        $this->assertStringContainsString( '::', $encrypted );

        $decrypted = $this->crypto->decrypt( $encrypted );
        $this->assertEquals( $original, $decrypted );
    }

    /**
     * Test encryption with special characters
     */
    public function test_encrypt_special_characters() {
        $original = 'test!@#$%^&*()_+-=[]{}|;:",.<>?/~`';

        $encrypted = $this->crypto->encrypt( $original );
        $decrypted = $this->crypto->decrypt( $encrypted );

        $this->assertEquals( $original, $decrypted );
    }

    /**
     * Test decrypt with invalid data
     */
    public function test_decrypt_invalid_data() {
        $result = $this->crypto->decrypt( 'invalid::data' );
        $this->assertFalse( $result );
    }

    /**
     * Test decrypt with empty string
     */
    public function test_decrypt_empty_string() {
        $result = $this->crypto->decrypt( '' );
        $this->assertFalse( $result );
    }

    /**
     * Test encrypt empty string
     */
    public function test_encrypt_empty_string() {
        $encrypted = $this->crypto->encrypt( '' );
        $decrypted = $this->crypto->decrypt( $encrypted );

        $this->assertEquals( '', $decrypted );
    }

    /**
     * Test multiple encryptions produce different results (due to random IV)
     */
    public function test_multiple_encryptions_different() {
        $original = 'test_data';

        $encrypted1 = $this->crypto->encrypt( $original );
        $encrypted2 = $this->crypto->encrypt( $original );

        $this->assertNotEquals( $encrypted1, $encrypted2 );

        // But both decrypt to same value
        $this->assertEquals( $original, $this->crypto->decrypt( $encrypted1 ) );
        $this->assertEquals( $original, $this->crypto->decrypt( $encrypted2 ) );
    }
}
