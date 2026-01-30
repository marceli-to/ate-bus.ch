<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'ATE Bus')</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 20px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; max-width: 600px; width: 100%;">
          {{-- Header --}}
          <tr>
            <td style="background-color: #1a365d; padding: 24px 30px;">
              <span style="color: #ffffff; font-size: 20px; font-weight: bold;">ATE Bus</span>
            </td>
          </tr>
          
          {{-- Content --}}
          <tr>
            <td style="padding: 30px;">
              @yield('content')
            </td>
          </tr>
          
          {{-- Footer --}}
          @hasSection('footer')
            <tr>
              <td style="padding: 0 30px 30px;">
                @yield('footer')
              </td>
            </tr>
          @endif
          
          {{-- Bottom bar --}}
          <tr>
            <td style="background-color: #f8fafc; padding: 20px 30px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;">
              © {{ date('Y') }} ATE Bus AG
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>