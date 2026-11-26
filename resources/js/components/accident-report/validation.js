import { hasSecondVehicle, isAlone, isNearMiss, triageComplete, usesQr } from './state.js';
import { needsControlCentre, stopName } from './report.js';

// Required answers per step, with the messages of the prototype.
// Returns '' when the step is complete, otherwise the message shown above the buttons.

const blank = (value) => !String(value ?? '').trim();
const anyBlank = (...values) => values.some(blank);

export function validateStep(s, config, step) {
  switch (s.type) {
    case 'vehicle':
      return vehicle(s, config, step);
    case 'staff':
      return staff(s, step);
    case 'passenger':
    case 'passenger_self':
      return passenger(s, config, step);
    default:
      return '';
  }
}

function vehicle(s, config, step) {
  const lineChosen = s.line && (s.line === 'none' || s.direction);

  if (step === 0) {
    return triageComplete(s) ? '' : 'Bitte alle Fragen beantworten.';
  }

  if (step === 1) {
    const vbz = needsControlCentre(s, config);
    const message = `Bitte Linie, Richtung${vbz ? ', Leitstelle informiert' : ''}, Ort, Fahrzeug und Namen ausfüllen.`;
    if (!lineChosen || (vbz && !s.controlCentreInformed)) return message;
    if (anyBlank(s.date, s.time, s.town) || !s.placeType) return message;
    if (s.placeType !== 'stop' && blank(s.street1)) return message;
    if (s.placeType === 'junction' && blank(s.street2)) return message;
    if (s.placeType === 'stop' && blank(stopName(s))) return message;
    if (!s.vehicleId || anyBlank(s.driverLastName, s.driverFirstName)) return message;
    return '';
  }

  if (step === 2) {
    if (isAlone(s) && s.aloneReason !== 'none') return '';
    if (!s.otherKind) return 'Bitte angeben, welches Fahrzeug noch beteiligt ist.';
    if (usesQr(s)) {
      return s.otherPartyDone && s.ownLicenceFront && s.ownLicenceBack
        ? ''
        : 'Bitte warten, bis Fahrzeug B fertig ist, und unsere Ausweise fotografieren.';
    }
    if (s.otherKind === 'fleet') {
      return s.secondVehicleId && !anyBlank(s.secondDriverLastName, s.secondDriverFirstName)
        ? ''
        : 'Bitte zweites Fahrzeug und Namen angeben.';
    }
    if (s.otherKind === 'external') {
      const o = s.other;
      const complete = o.vehicleType && o.licenceFront && o.licenceBack && o.registration && s.ownLicenceFront && s.ownLicenceBack
        && !anyBlank(o.phone, o.email) && (o.ownerIsDriver || !blank(o.ownerPhone));
      return complete ? '' : 'Bitte Art des Fahrzeugs, alle Fotos (Führerausweise vorne und hinten, Fahrzeugausweis), Telefon und E-Mail angeben.';
    }
    return s.damagedObjects.length || !blank(s.damagedObjectNote) ? '' : 'Bitte angeben, was beschädigt wurde.';
  }

  if (step === 3) {
    const together = hasSecondVehicle(s) && !isAlone(s);
    const message = `Bitte Manöver, Hergang${together ? ', Verursacher' : ''}, Aufprallpunkt und Schäden angeben.`;
    const damageA = s.damagesA.length || !blank(s.damageNoteA);
    if (!s.maneuversA.length || blank(s.course) || !damageA || !s.impactA) return message;
    if (together && !usesQr(s) && !s.impactB) return message;
    if (together) {
      const damageB = s.damagesB.length || !blank(s.damageNoteB);
      if (!s.causedBy || (!usesQr(s) && (!s.maneuversB.length || !damageB))) return message;
    }
    return '';
  }

  if (step === 4) {
    const both = hasSecondVehicle(s) && !isAlone(s);
    if (!s.signatureA || (both && !s.signatureB)) return both ? 'Bitte beide unterschreiben lassen.' : 'Bitte unterschreiben.';
    return '';
  }

  return '';
}

function staff(s, step) {
  const e = s.employee;

  if (step === 0) {
    const complete = !anyBlank(e.lastName, e.firstName, e.street, e.town, e.phone, e.email, e.birthDate, e.employedSince, e.nationality)
      && e.civilStatus && e.role;
    return complete ? '' : 'Bitte alle Felder ausfüllen.';
  }

  if (step === 1) {
    const dutyNumber = !/^vb/.test(s.lastDuty) || !blank(s.dutyNumber);
    const complete = s.eventKind && s.lastDuty && dutyNumber
      && !anyBlank(s.eventDate, s.eventTime, s.lastWorkDay, s.location, s.spot, s.activity, s.description);
    return complete ? '' : 'Bitte Art, Datum, Zeit, letzten Arbeitstag und Dienst, Ort, Stelle, Tätigkeit und Beschreibung angeben.';
  }

  if (step === 2) {
    if (isNearMiss(s)) return '';
    const complete = (s.injuredParts.length || !blank(s.injuredPartsNote)) && (s.injuryKinds.length || !blank(s.injuryNote)) && !blank(s.firstAid);
    return complete ? '' : 'Bitte verletzte Körperteile, Art der Verletzung und Erstbehandlung angeben.';
  }

  if (step === 3) {
    return s.signatureEmployee ? '' : 'Bitte unterschreiben.';
  }

  return '';
}

function passenger(s, config, step) {
  const self = s.type === 'passenger_self';
  const lineChosen = s.line && (s.line === 'none' || s.direction);
  const busAnswered = s.busState && (s.busState !== 'moving' || s.busMovements.length);

  if (!self && s.whoFills === 'card' && step === 1) {
    return s.cardHandedOver && !anyBlank(s.cardPassengerName, s.cardPassengerPhone)
      ? ''
      : 'Bitte Visitenkarte bestätigen sowie Name und Handy des Fahrgasts angeben.';
  }

  if (step === 0 && self) {
    const complete = busAnswered && lineChosen && !anyBlank(s.date, s.time, stopName(s));
    return complete ? '' : 'Bitte Datum, Zeit, Linie, Richtung, Haltestelle und «Der Bus stand / fuhr» angeben.';
  }

  if (step === 0) {
    const vbz = needsControlCentre(s, config);
    const complete = s.whoFills && busAnswered && lineChosen && (!vbz || s.controlCentreInformed)
      && !anyBlank(s.date, s.time, stopName(s)) && s.vehicleId && !anyBlank(s.driverLastName, s.driverFirstName);
    return complete
      ? ''
      : `Bitte Linie, Richtung${vbz ? ', Leitstelle informiert' : ''}, Haltestelle, «Der Bus stand / fuhr», Bus, Namen und «Wer füllt aus» angeben.`;
  }

  if (step === 1) {
    const p = s.passenger;
    return anyBlank(p.lastName, p.firstName, p.street, p.town, p.phone) ? 'Bitte Name, Vorname, Adresse und Telefon angeben.' : '';
  }

  if (step === 2) {
    const complete = (s.accidentKinds.length || !blank(s.passengerCourse)) && s.noticed !== null && s.reported !== null;
    return complete ? '' : 'Bitte Hergang und beide Ja/Nein-Fragen beantworten.';
  }

  if (step === 3) {
    return self || s.signatureA ? '' : 'Bitte als Mitarbeiter/in unterschreiben.';
  }

  return '';
}
