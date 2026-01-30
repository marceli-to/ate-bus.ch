@extends('emails.layouts.base')

@section('title', 'Bewerbungsbestätigung')

@section('content')
  <h1 style="margin: 0 0 20px; color: #1a365d; font-size: 24px;">
    Vielen Dank für Ihre Bewerbung
  </h1>

  <p>Guten Tag {{ $applicationData['firstname'] }} {{ $applicationData['lastname'] }},</p>

  <p>Wir haben Ihre Bewerbung für die Stelle <strong>{{ $applicationData['job_title'] }}</strong> erhalten und danken Ihnen für Ihr Interesse an einer Mitarbeit bei der ATE Bus.</p>

  <p>Wir werden Ihre Unterlagen sorgfältig prüfen und uns so bald wie möglich bei Ihnen melden.</p>

  <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>

  @include('emails.components.signature')
@endsection