<?php
/**
 * Email Notifications Class
 *
 * @package Sui_User_Wallets
 */

if (!defined('ABSPATH')) exit;

class SUW_Email_Notifications {

    /**
     * Send wallet creation email to user
     */
    public function send_wallet_created_email($user_id, $wallet_address) {
        $user = get_userdata($user_id);
        if (!$user) return false;

        $to = $user->user_email;
        $subject = 'Your Sui Wallet Has Been Created!';

        $message = $this->get_wallet_created_template($user, $wallet_address);

        $headers = array('Content-Type: text/html; charset=UTF-8');

        return wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Get wallet created email template
     */
    private function get_wallet_created_template($user, $wallet_address) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Your Sui Wallet</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #2271b1;">🎉 Your Sui Wallet is Ready!</h2>

                <p>Hello <?php echo esc_html($user->display_name); ?>,</p>

                <p>Great news! Your Sui blockchain wallet has been successfully created.</p>

                <div style="background: #f0f0f0; padding: 15px; border-radius: 4px; margin: 20px 0;">
                    <strong>Your Wallet Address:</strong><br>
                    <code style="font-size: 12px; word-break: break-all;"><?php echo esc_html($wallet_address); ?></code>
                </div>

                <h3>What's Next?</h3>
                <ul>
                    <li>You can now receive badges and NFTs</li>
                    <li>Complete courses to earn rewards</li>
                    <li>View your wallet in your <a href="<?php echo admin_url('profile.php'); ?>">profile</a></li>
                </ul>

                <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
                    <strong>⚠️ Important:</strong> This is a custodial wallet managed by the platform.
                    Your private key is securely encrypted and stored.
                </div>

                <p>
                    <a href="<?php echo admin_url('profile.php'); ?>" style="display: inline-block; padding: 10px 20px; background: #2271b1; color: white; text-decoration: none; border-radius: 4px;">
                        View My Wallet
                    </a>
                </p>

                <p>Questions? Contact support or visit the Help Center.</p>

                <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
                <p style="font-size: 12px; color: #666;">
                    This is an automated message from <?php echo get_bloginfo('name'); ?>.
                </p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
