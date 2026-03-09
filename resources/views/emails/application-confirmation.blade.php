@extends('emails.layouts.base')

@section('title', 'Bewerbungsbestätigung')

@section('content')
  <p class="email-text">{{ $applicationData['gender'] === 'frau' ? 'Sehr geehrte Frau' : 'Sehr geehrter Herr' }} {{ $applicationData['lastname'] }}</p>

  <p class="email-text">Vielen Dank für Ihre Bewerbung und Ihr Interesse an der ATE Bus AG.<br>Gerne prüfen wir Ihre Unterlagen sorgfältig und melden uns innerhalb einer Woche bei Ihnen.</p>

  <div class="signature">
    Freundliche Grüsse<br>
    <strong>ATE Bus AG</strong><br><br>
    Bietenholzstrasse 30<br>
    8307 Effretikon<br>
    <a href="mailto:bewerbung@ate-bus.ch" style="color: #02529F;">bewerbung@ate-bus.ch</a>
  </div>
@endsection
