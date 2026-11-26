// Accident report (Unfallmeldung): fixed lists and contact numbers, taken 1:1 from the approved prototype.

export const COMPANY = {
  name: 'ATE Bus AG',
  address: 'Bietenholzstrasse 30, 8307 Effretikon',
  phone: '052 355 12 00',
  email: 'info@ate-bus.ch',
  insurer: 'Zurich Versicherungs-Gesellschaft AG',
  policy: '15.609.538',
};

export const PHONE = {
  standby: '052 355 12 05', // Pikett
  vbzControlCentre: '044 411 46 41', // VBZ-Leitstelle
  police: '117',
  ambulance: '144',
};

export const tel = (number) => `tel:${number.replace(/\s/g, '')}`;

// What the vehicles did
export const MANEUVERS = [
  'stand still', 'stand an der Haltestelle', 'fuhr geradeaus', 'fuhr von der Haltestelle weg', 'bog rechts ab',
  'bog links ab', 'wechselte die Spur', 'überholte', 'fuhr rückwärts', 'fuhr in / im Kreisel',
  'fuhr aus Parkplatz / Ausfahrt', 'fuhr in Parkplatz / Einfahrt', 'fuhr auf (Auffahrunfall)', 'missachtete Vortritt / Rotlicht',
];

export const DAMAGES = ['Spiegel', 'Stossstange vorne', 'Stossstange hinten', 'Tür', 'Seitenwand', 'Scheibe', 'Licht', 'Kratzer', 'Delle'];

export const DAMAGED_OBJECTS = [
  'Geparktes Fahrzeug', 'Gartenzaun', 'Mauer', 'Signal / Pfosten', 'Haltestelle / Wartehalle', 'Gebäude', 'Baum / Hecke', 'Randstein', 'Leitplanke',
];

// Passenger accidents
export const PASSENGER_WHERE = ['Beim Einsteigen', 'Beim Aussteigen', 'Im Bus stehend', 'Im Bus sitzend', 'Im Gang / beim Platzsuchen', 'An der Haltestelle'];
export const BUS_MOVEMENTS = ['bremste', 'beschleunigte', 'fuhr eine Kurve'];
export const PASSENGER_ACCIDENT_KINDS = [
  'Sturz bei Bremsung', 'Sturz beim Anfahren', 'Sturz in Kurve', 'Von Tür eingeklemmt', 'Ausgerutscht / gestolpert', 'Sturz beim Ein- / Aussteigen',
];
export const UNABLE_TO_WORK = [
  { value: 'yes', label: 'Ja', report: 'Ja' },
  { value: 'no', label: 'Nein', report: 'Nein' },
  { value: 'unknown', label: 'Noch unbekannt', report: 'noch unbekannt' },
];

// Staff accidents
export const STAFF_EVENT_KINDS = [
  { value: 'work', label: 'Berufsunfall (während der Arbeit)', short: 'Berufsunfall' },
  { value: 'commute', label: 'Nichtberufsunfall – Arbeitsweg', short: 'Nichtberufsunfall (Arbeitsweg)' },
  { value: 'leisure', label: 'Nichtberufsunfall – Freizeit', short: 'Nichtberufsunfall (Freizeit)' },
  { value: 'near_miss', label: 'Beinahe-Unfall / Sachschaden (niemand verletzt)', short: 'Beinahe-Unfall / Sachschaden' },
];
export const STAFF_ROLES = ['Fahrer/in', 'Werkstatt', 'Reinigung', 'Büro / Disposition'];
export const CIVIL_STATUS = [
  { value: 'single', label: 'Ledig', report: 'Ledig' },
  { value: 'married', label: 'Verheiratet', report: 'Verheiratet' },
  { value: 'divorced', label: 'Geschieden', report: 'Geschieden' },
  { value: 'widowed', label: 'Verwitwet', report: 'Verwitwet' },
  { value: 'partnership', label: 'Eingetr. Partnerschaft', report: 'Eingetragene Partnerschaft' },
];
export const LAST_DUTY = [
  { value: 'workshop', label: 'Werkstatt' },
  { value: 'cleaning', label: 'Reinigung' },
  { value: 'vbg', label: 'Dienst VBG' },
  { value: 'vbz', label: 'Dienst VBZ' },
];
export const INJURY_KINDS = ['Prellung', 'Schnitt', 'Stich', 'Quetschung', 'Verstauchung / Zerrung', 'Bruch', 'Schürfung', 'Verbrennung', 'Fremdkörper im Auge'];

// Vehicle types of the other party (keys as validated by OtherPartyController)
export const VEHICLE_TYPES = ['bicycle', 'motorcycle', 'car', 'van', 'truck', 'bus'];
export const VEHICLE_TYPE_NAMES = { bus: 'Bus', car: 'Auto', van: 'Lieferwagen', truck: 'LKW', motorcycle: 'Motorrad', bicycle: 'Fahrrad' };

export const PLACE_TYPES = [
  { value: 'street', label: 'Strasse' },
  { value: 'junction', label: 'Kreuzung' },
  { value: 'stop', label: 'Haltestelle' },
  { value: 'car_park', label: 'Parkplatz' },
];

export const toOptions = (list) => list.map((value) => ({ value, label: value }));
