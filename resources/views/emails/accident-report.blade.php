<!DOCTYPE html>
<html lang="de">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f3f5f9;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#1d2430">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f5f9"><tr><td align="center" style="padding:16px 8px">
<table role="presentation" width="760" cellpadding="0" cellspacing="0" style="max-width:760px;width:100%;background:#ffffff"><tr><td style="padding:22px 26px">
@if ($isCopy)
<p style="margin:0 0 14px">Guten Tag<br><br>Sie erhalten eine Kopie der Unfallmeldung, die über das Online-Formular der ATE Bus AG erfasst wurde. Die Fotos sind im Anhang. Bei Fragen antworten Sie einfach auf diese E-Mail.</p>
<p style="margin:0 0 18px">Freundliche Grüsse<br>ATE Bus AG</p>
@else
<p style="margin:0 0 18px;color:#555">Neue Meldung über das Online-Formular. Fotos und der vollständige Bericht (Unfallbericht.html) sind im Anhang.</p>
@endif
{!! \App\Support\ReportHtml::embedImages($report, $message ?? null) !!}
<p style="margin:22px 0 0;color:#888;font-size:12px">ATE Bus AG · 052 355 12 00 · info@ate-bus.ch</p>
</td></tr></table>
</td></tr></table>
</body>
</html>
