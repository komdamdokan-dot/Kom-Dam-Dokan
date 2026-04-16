<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * Sends an OTP email for registration or password reset.
 */
function sendOTP(string $email, string $otp, string $purpose): bool
{
    $title = $purpose === 'forgot' ? 'Password Reset OTP' : 'Account Verification OTP';
    $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;background:#fff4e5;padding:24px;border-radius:16px;color:#222"><h2 style="margin:0 0 12px;color:#bf6b00">Kom Dam Dokan</h2><p style="margin:0 0 12px">Your OTP for ' . e($purpose) . ' is:</p><div style="font-size:32px;font-weight:700;letter-spacing:6px;margin:16px 0;color:#111">' . e($otp) . '</div><p style="margin:0">This OTP will expire in 10 minutes.</p></div>';
    return send_html_mail($email, $title, $html);
}

/**
 * Sends an order status update email.
 */
function sendOrderStatusEmail(string $email, string $orderNumber, string $status): bool
{
    $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;background:#eef7ff;padding:24px;border-radius:16px;color:#222"><h2 style="margin:0 0 12px;color:#0f5faa">Kom Dam Dokan</h2><p style="margin:0 0 8px">Your order <strong>' . e($orderNumber) . '</strong> status updated to <strong>' . e(ucfirst($status)) . '</strong>.</p><p style="margin:0">Thank you for shopping with us.</p></div>';
    return send_html_mail($email, 'Order Status Updated', $html);
}

/**
 * Sends a new order notification email to customer or admin.
 */
function sendOrderPlacedEmail(string $email, string $orderNumber, string $status = 'pending'): bool
{
    $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;background:#f7f8fb;padding:24px;border-radius:16px;color:#222"><h2 style="margin:0 0 12px;color:#2d5b37">Kom Dam Dokan</h2><p style="margin:0 0 8px">Order <strong>' . e($orderNumber) . '</strong> has been placed successfully.</p><p style="margin:0">Current status: <strong>' . e(ucfirst($status)) . '</strong>.</p></div>';
    return send_html_mail($email, 'Order Placed Successfully', $html);
}
