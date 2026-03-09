@extends('emails.layouts.base')

@section('title', 'Verlustmeldung bestätigt')

@section('content')
  <p class="email-text">{{ $reportData['gender'] === 'frau' ? 'Sehr geehrte Frau' : 'Sehr geehrter Herr' }} {{ $reportData['lastname'] }}</p>

  <p class="email-text">Vielen Dank für Ihre Nachricht. Wir kümmern uns schnellstmöglich um Ihre Meldung.</p>

  <p class="email-text"><strong>Ihre Angaben:</strong></p>

  @include('emails.components.data-table', ['rows' => [
    ['label' => 'Vorname', 'value' => $reportData['firstname']],
    ['label' => 'Nachname', 'value' => $reportData['lastname']],
    ['label' => 'E-Mail', 'value' => $reportData['email']],
    ['label' => 'Telefon', 'value' => $reportData['phone']],
    ['label' => 'Datum', 'value' => $reportData['date']],
    ['label' => 'Uhrzeit', 'value' => $reportData['time']],
    ['label' => 'Buslinie', 'value' => $reportData['bus_line']],
  ]])

  <p class="email-text"><strong>Verlorener Gegenstand:</strong><br>{{ $reportData['description'] }}</p>

  <p class="email-text"><strong>Hinweis:</strong><br>Bitte beachten Sie, dass gefundene Gegenstände ausschliesslich zur Abholung bereitliegen. Eine Zustellung ist nicht möglich.</p>

  <div class="signature">
    Freundliche Grüsse<br>
    <strong>ATE Bus AG</strong><br><br>
    Fundbüro Standort:<br>
    Breitistrasse 14<br>
    8307 Effretikon<br>
    <a href="mailto:fundgegenstaende@ate-bus.ch" style="color: #02529F;">fundgegenstaende@ate-bus.ch</a>
  </div>
@endsection
