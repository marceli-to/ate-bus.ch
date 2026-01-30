<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Neue Verlustmeldung</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; padding: 20px;">
  <h1 style="color: #1a365d;">Neue Verlustmeldung eingegangen</h1>

  <p>Eine neue Verlustmeldung wurde eingereicht:</p>

  <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
    <tr>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; width: 150px;">Name:</td>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $reportData['firstname'] }} {{ $reportData['lastname'] }}</td>
    </tr>
    <tr>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">E-Mail:</td>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $reportData['email'] }}</td>
    </tr>
    <tr>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Telefon:</td>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $reportData['phone'] }}</td>
    </tr>
    <tr>
      <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Datum:</td>
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
</body>
</html>
