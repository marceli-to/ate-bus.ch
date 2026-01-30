<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Neue Bewerbung</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; padding: 20px;">
    <h1 style="color: #1a365d;">Neue Bewerbung eingegangen</h1>

    <p>Eine neue Bewerbung wurde eingereicht:</p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; width: 150px;">Stelle:</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $applicationData['job_title'] }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Name:</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $applicationData['firstname'] }} {{ $applicationData['lastname'] }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">E-Mail:</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $applicationData['email'] }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Telefon:</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">{{ $applicationData['phone'] }}</td>
        </tr>
    </table>

    <div style="padding-top: 30px">
      <a href="{{ config('app.url') }}/cp/collections/bewerbungen/entries/{{ $applicationData['entry_id'] }}"
          style="display: inline-block; padding: 12px 24px; background-color: #1a365d; color: white; text-decoration: none;">
          Bewerbungsdaten
      </a>
    </div>
</body>
</html>
