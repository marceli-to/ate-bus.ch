@extends('emails.layouts.base')

@section('title', 'Bewerbungsbestätigung')

@section('content')
  <h1 class="email-title">Vielen Dank für Ihre Bewerbung</h1>

  <p class="email-text">Guten Tag {{ $applicationData['firstname'] }} {{ $applicationData['lastname'] }},</p>

  <p class="email-text">Wir haben Ihre Bewerbung für die Stelle <strong>{{ $applicationData['job_title'] }}</strong> erhalten und danken Ihnen für Ihr Interesse an einer Mitarbeit bei der ATE Bus.</p>

  <p class="email-text">Wir werden Ihre Unterlagen sorgfältig prüfen und uns so bald wie möglich bei Ihnen melden.</p>

  <p class="email-text">Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>

  @include('emails.components.signature')
@endsection
