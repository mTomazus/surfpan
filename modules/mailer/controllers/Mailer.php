<?php
class Mailer extends Trongate {

    public function _send_registration_received(string $to_email, string $to_name, string $comp_name, string $division_name): void {
        $subject = "Registration received – {$comp_name}";
        $html    = $this->_html_wrap($to_name, $subject,
            "<p>Your entry for <strong>" . htmlspecialchars($comp_name) . "</strong>
            in the <strong>" . htmlspecialchars($division_name) . "</strong> division
            has been received and is pending organiser approval.</p>
            <p>You will get another email once your registration is confirmed.</p>",
            'View your dashboard', BASE_URL . 'users/dashboard'
        );
        $this->_brevo_send($to_email, $to_name, $subject, $html);
    }

    public function _send_registration_confirmed(string $to_email, string $to_name, string $comp_name, string $division_name): void {
        $subject = "Registration confirmed – {$comp_name}";
        $html    = $this->_html_wrap($to_name, $subject,
            "<p>Great news! Your registration for <strong>" . htmlspecialchars($comp_name) . "</strong>
            in the <strong>" . htmlspecialchars($division_name) . "</strong> division
            has been confirmed.</p>
            <p>Keep an eye on your dashboard — we will notify you when the heat draw is published.</p>",
            'View your dashboard', BASE_URL . 'users/dashboard'
        );
        $this->_brevo_send($to_email, $to_name, $subject, $html);
    }

    public function _send_entry_receipt(string $to_email, string $to_name, string $comp_name, string $division_name, float $amount, string $currency, string $payment_ref, string $receipt_url): void {
        $subject = "Payment confirmed – {$comp_name}";
        $amt     = number_format($amount, 2) . ' ' . $currency;
        $html    = $this->_html_wrap($to_name, $subject,
            "<p>Your entry fee payment for <strong>" . htmlspecialchars($comp_name) . "</strong>
            (<strong>" . htmlspecialchars($division_name) . "</strong>) has been received.</p>
            <table style='width:100%;border-collapse:collapse;font-size:.9rem;margin:1rem 0'>
              <tr><td style='padding:4px 0;color:#666'>Amount</td><td style='text-align:right'><strong>" . htmlspecialchars($amt) . "</strong></td></tr>
              <tr><td style='padding:4px 0;color:#666'>Reference</td><td style='text-align:right'><code>" . htmlspecialchars($payment_ref) . "</code></td></tr>
            </table>
            <p>Your registration is now confirmed. We will notify you when the heat draw is published.</p>",
            'View receipt', $receipt_url
        );
        $this->_brevo_send($to_email, $to_name, $subject, $html);
    }

    public function _send_heat_draw_published(string $to_email, string $to_name, string $comp_name): void {
        $subject = "Heat draw published – {$comp_name}";
        $html    = $this->_html_wrap($to_name, $subject,
            "<p>The heat draw for <strong>" . htmlspecialchars($comp_name) . "</strong> is now live.</p>
            <p>Log in to see your heat number, jersey colour, and scheduled start time.</p>",
            'View heat draw', BASE_URL . 'users/dashboard'
        );
        $this->_brevo_send($to_email, $to_name, $subject, $html);
    }

    private function _brevo_send(string $to_email, string $to_name, string $subject, string $html): void {
        $payload = [
            'sender'      => ['name' => 'SurfPan', 'email' => 'info@surfpan.com'],
            'to'          => [['email' => $to_email, 'name' => $to_name]],
            'subject'     => $subject,
            'htmlContent' => $html,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.brevo.com/v3/smtp/email',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'api-key: ' . BREVO_API_KEY,
                'content-type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response  = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code >= 300) {
            $this->module('logger');
            $this->logger->log_message('error', "Mailer: Brevo rejected send to {$to_email} ({$http_code}): {$response}");
        }
    }

    private function _html_wrap(string $name, string $title, string $body_html, string $cta_label, string $cta_url): string {
        $name_safe  = htmlspecialchars($name);
        $title_safe = htmlspecialchars($title);
        $cta_label  = htmlspecialchars($cta_label);
        $cta_url    = htmlspecialchars($cta_url);
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title_safe}</title></head>
<body style="margin:0;padding:0;background:#f4f6fa;font-family:Arial,sans-serif;color:#1a1a2e;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fa;padding:40px 0;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">
        <tr><td style="background:#0d1b2a;padding:24px 32px;">
          <span style="color:#4ad9c7;font-size:20px;font-weight:700;letter-spacing:1px;">SURFPAN</span>
        </td></tr>
        <tr><td style="padding:32px;">
          <p style="margin:0 0 16px;font-size:15px;">Hi {$name_safe},</p>
          {$body_html}
          <p style="margin:28px 0 0;text-align:center;">
            <a href="{$cta_url}" style="display:inline-block;background:#4ad9c7;color:#0d1b2a;text-decoration:none;padding:12px 28px;border-radius:6px;font-weight:700;font-size:14px;">{$cta_label}</a>
          </p>
        </td></tr>
        <tr><td style="background:#f4f6fa;padding:16px 32px;text-align:center;font-size:12px;color:#666;">
          SurfPan &mdash; Surfing Competition Management
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }

}
