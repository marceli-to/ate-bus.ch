@extends('emails.layouts.base')

@section('title', 'Neue Verlustmeldung')

@section('content')
  <h1 style="margin: 0 0 20px; color: #1a365d; font-size: 24px;">
    Neue Verlustmeldung eingegangen
  </h1>

  <p>Eine neue Verlustmeldung wurde eingereicht:</p>

  @include('emails.components.data-table', ['rows' => [
    ['label' => 'Name', 'value' => $reportData['firstname'] . ' ' . $reportData['lastname']],
    ['label' => 'E-Mail', 'value' => $reportData['email']],
    ['label' => 'Telefon', 'value' => $reportData['phone']],
    ['label' => 'Datum', 'value' => $reportData['date']],
    ['label' => 'Uhrzeit', 'value' => $reportData['time']],
    ['label' => 'Buslinie', 'value' => $reportData['bus_line']],
    ['label' => 'Beschreibung', 'value' => $reportData['description']],
  ]])
@endsection