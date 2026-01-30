@extends('emails.layouts.base')

@section('title', 'Neue Bewerbung')

@section('content')
  <h1 class="email-title">Neue Bewerbung eingegangen</h1>

  <p class="email-text">Eine neue Bewerbung wurde eingereicht:</p>

  @include('emails.components.data-table', ['rows' => [
    ['label' => 'Stelle', 'value' => $applicationData['job_title']],
    ['label' => 'Name', 'value' => $applicationData['firstname'] . ' ' . $applicationData['lastname']],
    ['label' => 'E-Mail', 'value' => $applicationData['email']],
    ['label' => 'Telefon', 'value' => $applicationData['phone']],
  ]])

  @include('emails.components.button', [
    'href' => config('app.url') . '/cp/collections/applications/entries/' . $applicationData['entry_id'],
    'text' => 'Bewerbung ansehen'
  ])
@endsection
