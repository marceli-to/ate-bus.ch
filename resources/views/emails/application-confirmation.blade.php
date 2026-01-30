<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bewerbungsbestätigung</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; padding: 20px;">
  <h1 style="color: #1a365d;">Vielen Dank für Ihre Bewerbung</h1>

  <p>Guten Tag {{ $applicationData['firstname'] }} {{ $applicationData['lastname'] }},</p>

  <p>Wir haben Ihre Bewerbung für die Stelle <strong>{{ $applicationData['job_title'] }}</strong> erhalten und danken Ihnen für Ihr Interesse an einer Mitarbeit bei der ATE Bus.</p>

  <p>Wir werden Ihre Unterlagen sorgfältig prüfen und uns so bald wie möglich bei Ihnen melden.</p>

  <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>

  <div style="padding-top: 30px;">
    Freundliche Grüsse<br><strong>ATE Bus</strong>
  </div>
</body>
</html>
