import { externalTogether, isAlone, isCardHandover, usesQr } from './state.js';
import { PHONE, VEHICLE_TYPE_NAMES } from './constants.js';

// Texts for the report and the mail: subject, recipients, attachments. Wording 1:1 from the prototype.

export const formatDate = (iso) => {
  if (!iso) return '';
  const [y, m, d] = iso.split('-');
  return `${d}.${m}.${y}`;
};

export const joinNote = (list, note) => [list.join(', '), note].filter(Boolean).join(' – ');

export const yesNo = (value) => (value === true ? 'Ja' : value === false ? 'Nein' : '');

export const findVehicle = (data, id) => data.vehicles.find((vehicle) => vehicle.id === id) || null;
export const vehicleA = (s, data) => findVehicle(data, s.vehicleId);
export const vehicleB = (s, data) => findVehicle(data, s.secondVehicleId);
export const lineInfo = (s, data) => data.lines[s.line] || null;

export const stopName = (s) => (s.stop === '_other' ? s.stopOther : s.stop);

/** VBZ line, reported by staff: the VBZ control centre must be informed */
export const needsControlCentre = (s, data) => s.type !== 'passenger_self' && lineInfo(s, data)?.network === 'VBZ';

export function lineText(s, data) {
  if (s.line === 'none') return 'keine (Leerfahrt / PW)';
  const line = lineInfo(s, data);
  if (!line) return '';
  return `${s.line} (${line.network})${s.direction ? `, Richtung ${s.direction}` : ''}`;
}

export function placeText(s) {
  const stop = stopName(s);
  const place = {
    junction: `Kreuzung ${s.street1} / ${s.street2}`,
    stop: `Haltestelle ${stop}${s.street1 ? `, ${s.street1}` : ''}`,
    car_park: /^parkplatz/i.test(s.street1.trim()) ? s.street1 : `Parkplatz ${s.street1}`,
  }[s.placeType] ?? s.street1;
  const addTown = s.town && !(s.placeType === 'stop' && String(stop).startsWith(s.town));
  return place + (addTown ? `, ${s.town}` : '');
}

export const busText = (s) => {
  if (s.busState === 'stopped') return 'stand still';
  if (s.busState === 'moving') return `fuhr${s.busMovements.length ? ` – ${s.busMovements.join(', ')}` : ''}`;
  return '';
};

export const otherVehicleName = (type) => VEHICLE_TYPE_NAMES[type] || '';

/** Vehicle types shown on the impact sketch of our vehicle and of vehicle B */
export function impactTypesA(s, data) {
  const vehicle = vehicleA(s, data);
  return [vehicle?.category === 'Lieferwagen' ? 'van' : s.vehicleKind === 'car' ? 'car' : 'bus'];
}

export function impactTypesB(s, data, allTypes) {
  if (s.otherKind === 'fleet') {
    const vehicle = vehicleB(s, data);
    return [vehicle?.category === 'Lieferwagen' ? 'van' : s.secondVehicleKind === 'car' ? 'car' : 'bus'];
  }
  return s.other.vehicleType ? [s.other.vehicleType] : allTypes;
}

// ---------------------------------------------------------------- subject and recipients

export function subject(s, data) {
  const vehicle = vehicleA(s, data) || {};
  const line = lineInfo(s, data) ? ` – Linie ${s.line}` : '';

  if (s.type === 'staff') {
    const kind = { work: 'Berufsunfall', commute: 'Nichtberufsunfall (Arbeitsweg)', leisure: 'Nichtberufsunfall (Freizeit)', near_miss: 'Beinahe-Unfall / Sachschaden' }[s.eventKind] || '';
    return `Unfallmeldung Mitarbeiter/in – ${kind} – ${s.employee.lastName} ${s.employee.firstName} – ${formatDate(s.eventDate)}`;
  }

  if (isCardHandover(s)) {
    return `Unfall Fahrgast (Vormeldung, Visitenkarte abgegeben) – ${formatDate(s.date)} ${s.time}${line} – ${vehicle.label || ''}`;
  }

  if (s.type === 'passenger' || s.type === 'passenger_self') {
    const self = s.type === 'passenger_self';
    const bus = self ? (s.busNumber ? ` – Bus ${s.busNumber}` : '') : ` – ${vehicle.label || ''}`;
    return `Unfallmeldung Fahrgast${self ? ' (vom Fahrgast ausgefüllt)' : ''} – ${s.passenger.lastName} ${s.passenger.firstName} – ${formatDate(s.date)} ${s.time}${line}${bus}`;
  }

  const suffix = s.aloneReason === 'fled'
    ? ' – Fahrerflucht'
    : s.aloneReason === 'refused'
      ? ' – Unfallbeteiligter verweigert Mitwirkung'
      : s.otherKind === 'none'
        ? ' – Sachschaden'
        : s.otherKind === 'fleet'
          ? ` + ${vehicleB(s, data)?.label || ''} (intern)`
          : '';
  return `Unfallmeldung ATE Bus AG – ${formatDate(s.date)} ${s.time} – ${vehicle.label || ''} ${vehicle.plate || ''}${line}${suffix}`;
}

/** Who gets a copy (the other party, the owner of the damaged object, the passenger, the employee) */
export function copyTo(s) {
  if (s.type === 'staff') return s.employee.email;
  if (isCardHandover(s)) return '';
  if (s.type === 'passenger' || s.type === 'passenger_self') return s.passenger.email;
  if (s.otherKind === 'external') return isAlone(s) ? '' : s.other.email;
  if (s.otherKind === 'none') return s.objectOwnerEmail;
  return '';
}

export const recipient = (s, config) => (s.type === 'staff' ? config.recipients.staff : config.recipients.ate);

// ---------------------------------------------------------------- attachments

/**
 * Photos sent with the report, in the order of the prototype.
 * @returns {{name: string, data: string}[]}  data = data URL
 */
export function attachments(s) {
  const list = [];
  const add = (name, data) => {
    if (data) list.push({ name, data });
  };
  const extension = (data) => (/^data:image\/png/.test(data) ? 'png' : 'jpg');
  const numbered = (photos) => photos.forEach((data, i) => add(`Foto ${i + 1}.${extension(data)}`, data));

  if (s.type === 'vehicle') {
    if (externalTogether(s)) {
      add('Führerausweis ATE vorne.jpg', s.ownLicenceFront);
      add('Führerausweis ATE hinten.jpg', s.ownLicenceBack);
      if (!usesQr(s)) {
        add('Führerausweis Unfallbeteiligter vorne.jpg', s.other.licenceFront);
        add('Führerausweis Unfallbeteiligter hinten.jpg', s.other.licenceBack);
        add('Fahrzeugausweis Unfallbeteiligter.jpg', s.other.registration);
      }
    }
    numbered(s.photos);
  } else if (s.type === 'staff') {
    numbered(s.staffPhotos);
  } else if (!isCardHandover(s)) {
    numbered(s.photos);
  }

  return list;
}

export const controlCentreText = `informiert (${PHONE.vbzControlCentre})`;
