<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verlustmeldung bestätigt</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; padding: 20px;">
  <h1 style="color: #1a365d;">Vielen Dank für Ihre Verlustmeldung</h1>

  <p>Guten Tag {{ $reportData['firstname'] }} {{ $reportData['lastname'] }},</p>

  <p>Wir haben Ihre Verlustmeldung erhalten und werden prüfen, ob der beschriebene Gegenstand bei uns abgegeben wurde.</p>

  <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
    <tr>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; width: 150px;">Datum:</td>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $reportData['date'] }}</td>
    </tr>
    <tr>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Uhrzeit:</td>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $reportData['time'] }}</td>
    </tr>
    <tr>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Buslinie:</td>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $reportData['bus_line'] }}</td>
    </tr>
    <tr>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Beschreibung:</td>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $reportData['description'] }}</td>
    </tr>
  </table>

  <p>Sollte der Gegenstand bei uns abgegeben werden, melden wir uns bei Ihnen.</p>

  <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>

  <div style="padding-top: 30px;">
    Freundliche Grüsse<br><strong>ATE Bus</strong>
  </div>
</body>
</html>
