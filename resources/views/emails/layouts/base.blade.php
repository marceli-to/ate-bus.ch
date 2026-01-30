<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>@yield('title', 'ATE Bus')</title>
  
  <style>
    /* Reset */
    body, table, td, p, a, li { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
    
    /* Base */
    body { margin: 0; padding: 0; width: 100% !important; background-color: #f4f7fa; }
    
    /* Typography */
    .email-body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
    
    /* Container */
    .email-container { max-width: 560px; width: 100%; background-color: #ffffff; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
    
    /* Header */
    .email-header { background-color: #ffffff; padding: 32px 40px; border-bottom: 1px solid #e2e8f0; }
    .email-logo { max-width: 180px; height: auto; }
    
    /* Content */
    .email-content { padding: 40px; }
    .email-title { margin: 0 0 24px; color: #02529F; font-size: 26px; font-weight: 700; line-height: 1.3; letter-spacing: -0.5px; }
    .email-text { margin: 0 0 16px; color: #02529F; font-size: 16px; line-height: 1.7; }
    .email-text:last-child { margin-bottom: 0; }
    
    /* Data table */
    .data-table { width: 100%; margin: 24px 0; border-collapse: collapse; }
    .data-table td { padding: 14px 0; border-bottom: 1px solid #e2e8f0; font-size: 15px; line-height: 1.5; }
    .data-label { color: #14244E; font-weight: 500; width: 130px; vertical-align: top; }
    .data-value { color: #02529F; }
    .data-table tr:last-child td { border-bottom: none; }
    
    /* Button */
    .button-wrap { margin: 28px 0; }
    .email-button { display: inline-block; padding: 14px 32px; background-color: #02529F; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 15px; }
    
    /* Signature */
    .signature { margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0; color: #02529F; font-size: 15px; line-height: 1.6; }
    .signature strong { color: #02529F; }
    
    /* Footer */
    .email-footer { background-color: #f8fafc; padding: 24px 40px; text-align: center; }
    .footer-text { margin: 0; color: #14244E; font-size: 13px; line-height: 1.5; }
    
    /* Responsive */
    @media only screen and (max-width: 620px) {
      .email-container { width: 100% !important; margin: 0 !important; }
      .email-header { padding: 24px 24px !important; }
      .email-content { padding: 28px 24px !important; }
      .email-footer { padding: 20px 24px !important; }
      .email-title { font-size: 22px !important; }
      .email-text { font-size: 15px !important; }
      .data-label { width: 110px !important; font-size: 14px !important; }
      .data-value { font-size: 14px !important; }
      .email-button { padding: 12px 24px !important; font-size: 14px !important; }
      .email-logo { max-width: 150px !important; }
    }
  </style>
</head>
<body class="email-body">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f4f7fa;">
    <tr>
      <td align="center" style="padding: 40px 16px;">
        
        <table class="email-container" cellpadding="0" cellspacing="0" role="presentation">
          {{-- Header --}}
          <tr>
            <td class="email-header">
              <img src="{{ config('app.url') }}/img/logo-email.jpg" alt="ATE Bus" class="email-logo">
            </td>
          </tr>
          
          {{-- Content --}}
          <tr>
            <td class="email-content">
              @yield('content')
            </td>
          </tr>
          
          {{-- Footer --}}
          <tr>
            <td class="email-footer">
              <p class="footer-text">
                © {{ date('Y') }} ATE Bus AG · Alle Rechte vorbehalten
              </p>
            </td>
          </tr>
        </table>
        
      </td>
    </tr>
  </table>
</body>
</html>
