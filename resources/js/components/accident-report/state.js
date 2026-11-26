import { reactive, watch } from 'vue';

// Accident report state: one object for all report types, as in the approved prototype.
// Field names are English; values shown to people stay German.

const pad = (n) => String(n).padStart(2, '0');
export const today = () => {
  const d = new Date();
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
};
export const nowTime = () => {
  const d = new Date();
  return `${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const DRIVER_KEY = 'ate_accident_driver';
const EMPLOYEE_KEY = 'ate_accident_employee';
const EMPLOYEE_FIELDS = ['lastName', 'firstName', 'role', 'street', 'town', 'phone', 'email', 'birthDate', 'civilStatus', 'nationality', 'employedSince'];

const load = (key) => {
  try {
    return JSON.parse(localStorage.getItem(key) || '{}') || {};
  } catch {
    return {};
  }
};
const save = (key, value) => {
  try {
    localStorage.setItem(key, JSON.stringify(value));
  } catch {
    // private mode: carry on without remembering
  }
};

export function createState() {
  const driver = load(DRIVER_KEY);
  const employee = load(EMPLOYEE_KEY);

  const state = reactive({
    type: '', // vehicle | passenger | passenger_self | staff

    // When and where (vehicle and passenger)
    date: today(),
    time: nowTime(),
    line: '', // line number or 'none'
    direction: '',
    stop: '', // stop name or '_other'
    stopOther: '',
    controlCentreInformed: false, // VBZ lines
    placeType: '',
    street1: '',
    street2: '',
    town: '',

    // Our vehicle and driver
    vehicleKind: '', // bus | car
    vehicleId: '',
    driverLastName: driver.lastName || '',
    driverFirstName: driver.firstName || '',

    // Vehicle accident: first questions
    injured: null,
    together: '', // together | alone
    aloneReason: '', // fled | refused | none
    policeInformed: false,
    faultAgreed: null,
    digital: null,
    fillMode: '', // one (one phone) | qr (each on their own phone)

    // Who else was involved
    otherKind: '', // external | fleet | none
    other: {
      vehicleType: '',
      phone: '',
      ownerIsDriver: true,
      ownerPhone: '',
      email: '',
      licenceFront: null,
      licenceBack: null,
      registration: null,
      plate: '', // known details if the other party fled or refused
      colour: '',
      brand: '',
      driverDescription: '',
    },
    ownLicenceFront: null,
    ownLicenceBack: null,
    secondVehicleKind: '',
    secondVehicleId: '',
    secondDriverLastName: '',
    secondDriverFirstName: '',
    damagedObjects: [],
    damagedObjectNote: '',
    objectOwnerName: '',
    objectOwnerPhone: '',
    objectOwnerEmail: '',

    // QR flow: the other party fills in on their own phone
    otherPartyCode: '',
    otherPartyDone: false,
    otherPartyPhotos: [], // file names, the photos are already on the server

    // Course of the accident
    maneuversA: [],
    maneuversB: [],
    course: '',
    causedBy: '', // ate | other | both
    damagesA: [],
    damageNoteA: '',
    damagesB: [],
    damageNoteB: '',
    impactA: null, // { type, x, y }
    impactB: null,
    sketch: { background: 'street', elements: [] },
    photos: [],
    witnesses: [],
    signatureA: null,
    signatureB: null,

    // Passenger accident
    whoFills: '', // now | card
    cardHandedOver: false,
    cardPassengerName: '', // business card handed over: who got it
    cardPassengerPhone: '',
    passenger: {
      lastName: '',
      firstName: '',
      street: '',
      town: '',
      phone: '',
      email: '',
      birthDate: '',
      occupation: '',
      employer: '',
      signature: null,
    },
    where: [],
    whereNote: '',
    busState: '', // stopped | moving
    busMovements: [],
    busNumber: '', // passenger's own report
    accidentKinds: [],
    passengerCourse: '',
    noticed: null,
    reported: null,
    bodyParts: [],
    consequences: '',
    doctor: '',
    unableToWork: '',
    unableToWorkDuration: '',
    insurance: '',
    propertyDamage: '',
    remarks: '',

    // Staff accident
    employee: {
      lastName: '',
      firstName: '',
      role: '',
      street: '',
      town: '',
      phone: '',
      email: '',
      birthDate: '',
      civilStatus: '',
      nationality: '',
      employedSince: '',
      ...Object.fromEntries(EMPLOYEE_FIELDS.filter((key) => employee[key]).map((key) => [key, employee[key]])),
    },
    eventKind: '',
    eventDate: today(),
    eventTime: nowTime(),
    lastWorkDay: '',
    workEnd: '',
    lastDuty: '',
    dutyNumber: '',
    location: '',
    spot: '',
    activity: '',
    description: '',
    prevention: '',
    staffPhotos: [],
    injuredParts: [],
    injuredPartsNote: '',
    injuryKinds: [],
    injuryNote: '',
    firstAid: '',
    staffPropertyDamage: '',
    signatureEmployee: null,
  });

  applyRules(state);

  return state;
}

/**
 * An answer that changes the path resets the answers that depend on it (as in the prototype).
 * Synchronous, so data taken over from the other party in one go is not wiped afterwards.
 */
function applyRules(s) {
  const on = (source, reset) => watch(source, reset, { flush: 'sync' });

  on(() => s.vehicleKind, () => { s.vehicleId = ''; s.impactA = null; });
  on(() => s.secondVehicleKind, () => { s.secondVehicleId = ''; s.impactB = null; });
  on(() => s.other.vehicleType, () => { s.impactB = null; });
  on(() => s.otherKind, () => { s.impactB = null; s.other.vehicleType = ''; });
  on(() => s.injured, () => { s.faultAgreed = null; s.together = ''; s.aloneReason = ''; s.digital = null; });
  on(() => s.together, () => {
    s.faultAgreed = null;
    s.aloneReason = '';
    s.policeInformed = false;
    s.otherKind = '';
    s.digital = null;
  });
  on(() => s.faultAgreed, () => { s.digital = null; });
  on(() => s.digital, () => { s.fillMode = ''; });
  on(() => s.aloneReason, (reason) => {
    s.otherKind = !reason ? '' : reason === 'none' ? 'none' : 'external';
    s.policeInformed = false;
  });
  on(() => s.line, () => { s.direction = ''; s.stop = ''; s.controlCentreInformed = false; });
  on(() => s.direction, () => { s.stop = ''; });
}

export function rememberDriver(s) {
  save(DRIVER_KEY, { lastName: s.driverLastName, firstName: s.driverFirstName });
}

export function rememberEmployee(s) {
  save(EMPLOYEE_KEY, Object.fromEntries(EMPLOYEE_FIELDS.map((key) => [key, s.employee[key]])));
}

// ---------------------------------------------------------------- derived values

export const isAlone = (s) => s.together === 'alone';
export const hasSecondVehicle = (s) => s.otherKind === 'external' || s.otherKind === 'fleet';
export const usesQr = (s) => s.otherKind === 'external' && s.fillMode === 'qr' && s.together === 'together';
// External party present at the scene (not fled / refused)
export const externalTogether = (s) => s.otherKind === 'external' && !isAlone(s);
export const isNearMiss = (s) => s.eventKind === 'near_miss';
export const isCardHandover = (s) => s.type === 'passenger' && s.whoFills === 'card';

/** Type sent to the server: the business-card handover is its own type */
export const submitType = (s) => (isCardHandover(s) ? 'passenger_card' : s.type);

export function stepsFor(s) {
  switch (s.type) {
    case 'staff':
      return ['Person', 'Ereignis', isNearMiss(s) ? 'Sachschaden' : 'Verletzung', 'Prüfen und unterschreiben'];
    case 'passenger':
      return s.whoFills === 'card' ? ['Fahrt', 'Visitenkarte'] : ['Fahrt', 'Fahrgast', 'Hergang und Folgen', 'Prüfen'];
    case 'passenger_self':
      return ['Ihre Fahrt', 'Ihre Angaben', 'Hergang und Folgen', 'Prüfen und senden'];
    default:
      return ['Ausschluss', 'Ort und Fahrzeug', 'Beteiligte', 'Hergang und Schaden', 'Prüfen'];
  }
}

/** Vehicle accident, first step: answered so that the form may be filled in */
export function triageComplete(s) {
  if (s.injured !== false) return false;
  if (s.together === 'together') return s.faultAgreed === true && s.digital === true && !!s.fillMode;
  if (s.together === 'alone') return s.aloneReason === 'none' || (!!s.aloneReason && s.policeInformed);
  return false;
}
