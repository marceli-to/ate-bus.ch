@extends('emails.layouts.base')

@section('title', 'Verlustmeldung bestätigt')

@section('content')
  <h1 class="email-title">Vielen Dank für Ihre Verlustmeldung</h1>

  <p class="email-text">Guten Tag {{ $reportData['firstname'] }} {{ $reportData['lastname'] }},</p>

  <p class="email-text">Wir haben Ihre Verlustmeldung erhalten und werden prüfen, ob der beschriebene Gegenstand bei uns abgegeben wurde.</p>

  @include('emails.components.data-table', ['rows' => [
    ['label' => 'Datum', 'value' => $reportData['date']],
    ['label' => 'Uhrzeit', 'value' => $reportData['time']],
    ['label' => 'Buslinie', 'value' => $reportData['bus_line']],
    ['label' => 'Beschreibung', 'value' => $reportData['description']],
  ]])

  <p class="email-text">Sollte der Gegenstand bei uns abgegeben werden, melden wir uns bei Ihnen.</p>

  <p class="email-text">Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>

  @include('emails.components.signature')
@endsection
