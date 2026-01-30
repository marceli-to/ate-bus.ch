@extends('emails.layouts.base')

@section('title', 'Neue Verlustmeldung')

@section('content')
  <h1 class="email-title">Neue Verlustmeldung eingegangen</h1>

  <p class="email-text">Eine neue Verlustmeldung wurde eingereicht:</p>

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
