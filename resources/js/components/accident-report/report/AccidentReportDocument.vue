<template>
  <div style="font-family:Arial,Helvetica,sans-serif;font-size:13.5px;color:#1f2733">
    <!-- Letterhead -->
    <template v-if="full">
      <table width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;border-bottom:3px solid #1355A1">
        <tbody>
          <tr>
            <td style="padding:0 0 10px 0;vertical-align:middle"><img :src="logo" alt="ATE Bus AG" width="131" height="40" style="width:131px;height:40px" /></td>
            <td style="padding:0 0 10px 0;vertical-align:middle;text-align:right;font-size:12px;color:#666666">
              {{ COMPANY.name }}<br>{{ COMPANY.address }}<template v-if="!cardHandover"><br>Tel. {{ COMPANY.phone }} · {{ COMPANY.email }}</template>
            </td>
          </tr>
        </tbody>
      </table>
      <div style="font-size:22px;font-weight:bold;color:#1355A1;margin:16px 0 2px">{{ heading.title }}</div>
      <div style="color:#6b7280;font-size:12px;margin-bottom:14px">{{ heading.meta }}</div>
    </template>

    <!-- ================================================= vehicle accident -->
    <template v-if="s.type === 'vehicle'">
      <ReportSection title="1 · Zeit und Ort">
        <ReportRow label="Datum / Zeit" :value="`${formatDate(s.date)}, ${s.time} Uhr`" />
        <ReportRow label="Unfallort" :value="placeText(s)" />
        <ReportRow label="Linie / Richtung" :value="lineText(s, config)" />
        <ReportRow v-if="needsControlCentre(s, config)" label="VBZ-Leitstelle" :value="controlCentreText" />
        <ReportRow label="Verletzte" value="Nein" />
        <ReportRow label="Ausgefüllt" :value="alone ? `nur von Mitarbeiter/in – ${reason}` : 'gemeinsam mit dem Unfallbeteiligten'" />
        <ReportRow v-if="s.aloneReason === 'fled' || s.aloneReason === 'refused'" label="Polizei" value="informiert" />
        <ReportRow label="Art" :value="{ external: 'Kollision mit fremdem Fahrzeug', fleet: 'Kollision zweier Fahrzeuge der ATE Bus AG' }[s.otherKind] || 'Sachschaden ohne anderes Fahrzeug'" />
      </ReportSection>

      <ReportSection title="2 · Fahrzeug A – ATE Bus AG">
        <ReportRow label="Fahrzeug" :value="vA.label" />
        <ReportRow label="Kontrollschild" :value="vA.plate" />
        <ReportRow label="Stammnummer" :value="vA.chassis_number" />
        <ReportRow label="Typ" :value="`${vA.model || ''} (${vA.category || ''})`" />
        <ReportRow label="Halter" :value="holder(vA)" />
        <ReportRow label="Telefon Halter" :value="COMPANY.phone" />
        <ReportRow label="Versicherung" :value="`${COMPANY.insurer}\n${COMPANY.policy}`" />
        <ReportRow label="Mitarbeiter/in" :value="`${s.driverLastName} ${s.driverFirstName}`" />
        <ReportRow v-if="external" label="Führerausweis" :value="photoNote(s.ownLicenceFront, s.ownLicenceBack)" />
        <ReportRow label="Manöver" :value="s.maneuversA.join(', ')" />
        <ReportRow label="Schaden" :value="joinNote(s.damagesA, s.damageNoteA)" />
      </ReportSection>

      <ReportSection v-if="s.otherKind === 'fleet'" title="3 · Fahrzeug B – ATE Bus AG">
        <ReportRow label="Fahrzeug" :value="vB.label" />
        <ReportRow label="Kontrollschild" :value="vB.plate" />
        <ReportRow label="Stammnummer" :value="vB.chassis_number" />
        <ReportRow label="Typ" :value="`${vB.model || ''} (${vB.category || ''})`" />
        <ReportRow label="Halter" :value="holder(vB)" />
        <ReportRow label="Mitarbeiter/in" :value="`${s.secondDriverLastName} ${s.secondDriverFirstName}`" />
        <ReportRow label="Manöver" :value="s.maneuversB.join(', ')" />
        <ReportRow label="Schaden" :value="joinNote(s.damagesB, s.damageNoteB)" />
      </ReportSection>

      <ReportSection v-if="s.otherKind === 'external' && alone" title="3 · Fahrzeug B – Unfallbeteiligter (soweit bekannt)" dark>
        <ReportRow label="Art" :value="otherVehicleName(s.other.vehicleType)" />
        <ReportRow label="Kennzeichen" :value="s.other.plate" />
        <ReportRow label="Marke / Typ" :value="s.other.brand" />
        <ReportRow label="Farbe" :value="s.other.colour" />
        <ReportRow label="Lenker/in" :value="s.other.driverDescription" />
        <ReportRow v-if="s.other.phone" label="Handy" :value="s.other.phone" />
        <ReportRow label="Manöver" :value="s.maneuversB.join(', ')" />
        <ReportRow label="Schaden" :value="joinNote(s.damagesB, s.damageNoteB)" />
      </ReportSection>

      <ReportSection v-if="external" title="3 · Fahrzeug B – Unfallbeteiligter" dark>
        <ReportRow label="Art" :value="otherVehicleName(s.other.vehicleType)" />
        <ReportRow label="Führerausweis" :value="photoNote(otherPhoto('licenceFront'), otherPhoto('licenceBack'))" />
        <ReportRow label="Fahrzeugausweis" :value="otherPhoto('registration') ? 'Foto im Anhang (inkl. Versicherung)' : ''" />
        <ReportRow label="Telefon Unfallbeteiligter" :value="s.other.phone" />
        <ReportRow label="Halter" :value="s.other.ownerIsDriver ? 'gleich wie Unfallbeteiligter' : 'anderer Halter'" />
        <ReportRow label="Telefon Halter" :value="s.other.ownerIsDriver ? s.other.phone : s.other.ownerPhone" />
        <ReportRow label="E-Mail" :value="s.other.email" />
        <ReportRow label="Manöver" :value="s.maneuversB.join(', ')" />
        <ReportRow label="Schaden" :value="joinNote(s.damagesB, s.damageNoteB)" />
      </ReportSection>

      <ReportSection v-if="s.otherKind === 'none'" title="3 · Beschädigtes Objekt" dark>
        <ReportRow label="Objekt" :value="joinNote(s.damagedObjects, s.damagedObjectNote)" />
        <ReportRow label="Eigentümer" :value="s.objectOwnerName" />
        <ReportRow label="Telefon" :value="s.objectOwnerPhone" />
        <ReportRow label="E-Mail" :value="s.objectOwnerEmail" />
      </ReportSection>

      <ReportSection title="4 · Zeugen">
        <WitnessRows :witnesses="s.witnesses" prefix="Zeuge" />
      </ReportSection>

      <ReportSection title="5 · Unfallhergang">
        <ReportRow :value="s.course" />
      </ReportSection>

      <ReportSection v-if="s.impactA || s.impactB" title="Erster Aufprallpunkt">
        <tr>
          <td :style="`${CELL};width:50%;text-align:center`">
            A<br>
            <ReportPicture v-if="s.impactA" :svg="impactSvg([s.impactA.type], s.impactA, { readonly: true })" :png="mail?.impactA" :width="92" :height="170" alt="Aufprallpunkt A" />
            <template v-else>–</template>
          </td>
          <td v-if="hasSecondVehicle(s)" :style="`${CELL};text-align:center`">
            B{{ s.impactB ? ` – ${otherVehicleName(s.impactB.type)}` : '' }}<br>
            <ReportPicture v-if="s.impactB" :svg="impactSvg([s.impactB.type], s.impactB, { readonly: true })" :png="mail?.impactB" :width="92" :height="170" alt="Aufprallpunkt B" />
            <template v-else>–</template>
          </td>
        </tr>
      </ReportSection>

      <ReportSection v-if="s.sketch.elements.length" title="Unfallskizze">
        <tr>
          <td colspan="2" :style="`${CELL};text-align:center`">
            <ReportPicture :svg="sketchSvg(s.sketch, sketchVehicles)" :png="mail?.sketch" :width="260" :height="260" alt="Unfallskizze" />
          </td>
        </tr>
      </ReportSection>

      <ReportSection title="6 · Schuldfrage">
        <ReportRow v-if="alone" label="Geklärt" :value="s.aloneReason === 'none' ? 'Sachschaden ohne anderes Fahrzeug' : `Nein – ${reason}`" />
        <template v-else>
          <ReportRow label="Geklärt" value="Ja, vor Ort einvernehmlich" />
          <ReportRow v-if="bothSign" label="Verursacher" :value="causedBy" />
        </template>
      </ReportSection>

      <ReportSection title="7 · Fotos">
        <ReportRow><ReportPhotos :items="vehiclePhotos" :mail="!!mail" /></ReportRow>
      </ReportSection>

      <ReportSection v-if="full" title="8 · Bestätigung">
        <tr>
          <td :style="`${CELL};width:50%`">Mitarbeiter/in A – ATE Bus AG<br><SignatureImage :src="s.signatureA" /></td>
          <td v-if="bothSign" :style="CELL">
            {{ s.otherKind === 'fleet' ? 'Mitarbeiter/in B – ATE Bus AG' : 'Unfallbeteiligter B' }}<br><SignatureImage :src="s.signatureB" />
          </td>
        </tr>
      </ReportSection>
      <div v-if="full" style="font-size:11px;color:#666666;margin-top:4px">Die Unterschrift bestätigt die Angaben und ist keine Schuldanerkennung.</div>
    </template>

    <!-- ================================================= staff accident -->
    <template v-else-if="s.type === 'staff'">
      <ReportSection title="1 · Art des Ereignisses">
        <ReportRow label="Art"><b>{{ eventKind.short }}</b></ReportRow>
      </ReportSection>

      <ReportSection title="2 · Betroffene Person">
        <ReportRow label="Name, Vorname" :value="`${e.lastName} ${e.firstName}`" />
        <ReportRow label="Funktion" :value="e.role" />
        <ReportRow label="Adresse" :value="`${e.street}, ${e.town}`" />
        <ReportRow label="Handy" :value="e.phone" />
        <ReportRow label="E-Mail" :value="e.email" />
        <ReportRow label="Geburtsdatum" :value="formatDate(e.birthDate)" />
        <ReportRow label="Zivilstand" :value="CIVIL_STATUS.find((c) => c.value === e.civilStatus)?.report || ''" />
        <ReportRow label="Staatsangehörigkeit" :value="e.nationality" />
        <ReportRow label="Angestellt seit" :value="formatDate(e.employedSince)" />
      </ReportSection>

      <ReportSection title="3 · Ereignis">
        <ReportRow label="Datum / Uhrzeit" :value="`${formatDate(s.eventDate)}, ${s.eventTime} Uhr`" />
        <ReportRow label="Letzter Arbeitstag vorher" :value="`${formatDate(s.lastWorkDay)}${s.workEnd ? `, Arbeitsende ${s.workEnd} Uhr` : ''}`" />
        <ReportRow label="Letzter Dienst" :value="lastDuty" />
        <ReportRow label="Ort" :value="s.location" />
        <ReportRow label="Stelle" :value="s.spot" />
        <ReportRow label="Tätigkeit" :value="s.activity" />
      </ReportSection>

      <ReportSection title="4 · Was ist passiert?">
        <ReportRow :value="s.description" />
        <ReportRow label="Verhindern durch" :value="s.prevention" />
      </ReportSection>

      <ReportSection v-if="!nearMiss" title="5 · Verletzung">
        <ReportRow label="Verletzte Körperteile" :value="joinNote(s.injuredParts, s.injuredPartsNote)" />
        <ReportRow label="Art der Verletzung" :value="joinNote(s.injuryKinds, s.injuryNote)" />
        <ReportRow label="Erstbehandlung" :value="s.firstAid" />
      </ReportSection>

      <ReportSection :title="`${nearMiss ? 5 : 6} · Sachschaden`">
        <ReportRow label="Sachschaden" :value="s.staffPropertyDamage" />
      </ReportSection>

      <ReportSection :title="`${nearMiss ? 6 : 7} · Beteiligte Personen / Zeugen`">
        <WitnessRows :witnesses="s.witnesses" prefix="Person" />
      </ReportSection>

      <ReportSection v-if="s.staffPhotos.length" title="Fotos / Skizze">
        <ReportRow><ReportPhotos :items="photoItems(s.staffPhotos)" :mail="!!mail" /></ReportRow>
      </ReportSection>

      <template v-if="full">
        <ReportSection title="Unterschrift">
          <ReportRow>Mitarbeiter/in {{ e.lastName }} {{ e.firstName }}<br><SignatureImage :src="s.signatureEmployee" /></ReportRow>
        </ReportSection>
        <div v-if="!nearMiss" style="margin-top:10px;padding:10px 12px;border-left:5px solid #C62828;background:#FDECEA;font-size:13px">
          <b>Wichtig:</b> Bitte bei den behandelnden Ärztinnen und Ärzten immer die SUVA-Nummer bekannt geben. Du bekommst sie in den nächsten Tagen von der Personalabteilung.
        </div>
      </template>
    </template>

    <!-- ================================================= passenger: business card handed over (pre-report) -->
    <template v-else-if="cardHandover">
      <ReportSection title="Zeit und Ort">
        <PassengerTripRows :s="s" :config="config" />
        <ReportRow label="Fahrzeug" :value="vA.label ? `${vA.label} · ${vA.plate}` : ''" />
        <ReportRow label="Mitarbeiter/in" :value="`${s.driverLastName} ${s.driverFirstName}`" />
      </ReportSection>
      <ReportSection title="Fahrgast">
        <ReportRow label="Name" :value="s.cardPassengerName" />
        <ReportRow label="Handy" :value="s.cardPassengerPhone" />
        <ReportRow label="Visitenkarte" value="abgegeben" />
      </ReportSection>
    </template>

    <!-- ================================================= passenger accident -->
    <template v-else>
      <ReportSection title="1 · Fahrgast">
        <ReportRow label="Name / Vorname" :value="`${p.lastName} ${p.firstName}`" />
        <ReportRow label="Strasse" :value="p.street" />
        <ReportRow label="PLZ / Ort" :value="p.town" />
        <ReportRow label="Telefon" :value="p.phone" />
        <ReportRow label="E-Mail" :value="p.email" />
        <ReportRow label="Geburtsdatum" :value="formatDate(p.birthDate)" />
        <ReportRow label="Beruf" :value="p.occupation" />
        <ReportRow label="Arbeitgeber" :value="p.employer" />
      </ReportSection>

      <ReportSection title="2 · Zeit und Ort">
        <PassengerTripRows :s="s" :config="config" />
      </ReportSection>

      <ReportSection title="3 · Hergang">
        <ReportRow label="Art" :value="s.accidentKinds.join(', ')" />
        <ReportRow :value="s.passengerCourse" />
        <ReportRow label="Von ATE-Mitarbeiter/in bemerkt" :value="yesNo(s.noticed)" />
        <ReportRow label="Der ATE-Mitarbeiterin / dem ATE-Mitarbeiter gemeldet" :value="yesNo(s.reported)" />
      </ReportSection>

      <ReportSection title="4 · Zeugen">
        <WitnessRows :witnesses="s.witnesses" prefix="Zeuge" />
      </ReportSection>

      <ReportSection title="5 · Folgen">
        <ReportRow label="Verletzte Körperteile" :value="s.bodyParts.join(', ')" />
        <ReportRow label="Unfallfolgen" :value="s.consequences" />
        <ReportRow label="Arzt / Spital" :value="s.doctor" />
        <ReportRow label="Arbeitsunfähig" :value="UNABLE_TO_WORK.find((u) => u.value === s.unableToWork)?.report || ''" />
        <ReportRow v-if="s.unableToWork === 'yes'" label="Dauer" :value="s.unableToWorkDuration" />
        <ReportRow label="Eigene Versicherung" :value="s.insurance" />
        <ReportRow label="Sachschaden" :value="s.propertyDamage" />
        <ReportRow label="Bemerkungen" :value="s.remarks" />
      </ReportSection>

      <ReportSection :title="selfReport ? '6 · Fahrzeug' : '6 · Fahrzeug und Mitarbeiter/in'">
        <ReportRow v-if="selfReport" label="Busnummer (Angabe Fahrgast)" :value="s.busNumber" />
        <template v-else>
          <ReportRow label="Fahrzeug" :value="vA.label ? `${vA.label} · ${vA.plate}` : ''" />
          <ReportRow label="Mitarbeiter/in" :value="`${s.driverLastName} ${s.driverFirstName}`" />
        </template>
      </ReportSection>

      <ReportSection v-if="s.photos.length" title="7 · Fotos">
        <ReportRow><ReportPhotos :items="photoItems(s.photos)" :mail="!!mail" /></ReportRow>
      </ReportSection>

      <template v-if="full">
        <ReportSection title="Unterschriften">
          <tr>
            <td :style="`${CELL};width:50%`">Fahrgast<br><SignatureImage :src="p.signature" /></td>
            <td v-if="!selfReport" :style="CELL">Mitarbeiter/in ATE Bus AG<br><SignatureImage :src="s.signatureA" /></td>
          </tr>
        </ReportSection>
        <div style="font-size:11px;color:#666666;margin-top:4px">Die Abgabe dieser Unfall- / Schadenmeldung begründet weder einen Nachweis noch eine Haftung.</div>
      </template>
    </template>
  </div>
</template>

<script setup>
import { computed, h } from 'vue';
import ReportSection from './ReportSection.vue';
import ReportRow from './ReportRow.vue';
import ReportPhotos from './ReportPhotos.vue';
import ReportPicture from './ReportPicture.vue';
import logo from './ate-logo.jpg?inline';
import { COMPANY, CIVIL_STATUS, LAST_DUTY, STAFF_EVENT_KINDS, UNABLE_TO_WORK } from '../constants.js';
import { externalTogether, hasSecondVehicle, isAlone, isCardHandover, isNearMiss, usesQr } from '../state.js';
import {
  attachments, busText, controlCentreText, formatDate, joinNote, lineText, needsControlCentre, otherVehicleName,
  placeText, stopName, vehicleA, vehicleB, yesNo,
} from '../report.js';
import { impactSvg, sketchSvg } from '../svg.js';

// The accident report: preview before signing (full = false), success page and mail body (full = true).
// Inline styles and tables only, because the rendered HTML is sent as the mail.
const props = defineProps({
  state: {
    type: Object,
    required: true,
  },
  config: {
    type: Object,
    required: true,
  },
  full: {
    type: Boolean,
    default: false,
  },
  // Mail version: { impactA, impactB, sketch } as PNG data URLs; photos become «📎 name (Anhang)»
  mail: {
    type: Object,
    default: null,
  },
});

const CELL = 'border:1px solid #d6deea;padding:6px 9px;vertical-align:top;background:#ffffff;font-size:13.5px;font-family:Arial,Helvetica,sans-serif';

const s = props.state;
const config = props.config;
const e = s.employee;
const p = s.passenger;

const vA = computed(() => vehicleA(s, config) || {});
const vB = computed(() => vehicleB(s, config) || {});
const alone = computed(() => isAlone(s));
const external = computed(() => externalTogether(s));
// Both parties sign: second vehicle present and filled in together
const bothSign = computed(() => hasSecondVehicle(s) && !alone.value);
const cardHandover = computed(() => isCardHandover(s));
const selfReport = computed(() => s.type === 'passenger_self');
const nearMiss = computed(() => isNearMiss(s));
const eventKind = computed(() => STAFF_EVENT_KINDS.find((kind) => kind.value === s.eventKind) || { short: '' });

const reason = computed(() => ({
  fled: 'Unfallbeteiligter ist geflüchtet',
  refused: 'Unfallbeteiligter verweigert die Zusammenarbeit',
  none: 'kein anderes Fahrzeug beteiligt',
}[s.aloneReason] || ''));

const causedBy = computed(() => (s.otherKind === 'fleet'
  ? { ate: `Mitarbeiter/in A (${s.driverLastName} ${s.driverFirstName})`, other: `Mitarbeiter/in B (${s.secondDriverLastName} ${s.secondDriverFirstName})`, both: 'Beide' }
  : { ate: 'Mitarbeiter/in ATE Bus AG', other: 'Unfallbeteiligter', both: 'Beide Parteien' })[s.causedBy] || '');

const lastDuty = computed(() => {
  const duty = LAST_DUTY.find((d) => d.value === s.lastDuty)?.label || '';
  return duty + (s.dutyNumber && /^vb/.test(s.lastDuty) ? ` Nr. ${s.dutyNumber}` : '');
});

const sketchVehicles = computed(() => ({
  busA: s.vehicleKind === 'bus',
  busB: s.otherKind === 'fleet' && s.secondVehicleKind === 'bus',
}));

const heading = computed(() => {
  const created = new Date().toLocaleString('de-CH');
  if (s.type === 'staff') {
    return { title: 'Unfallmeldung Mitarbeiter/in', meta: `Aufbau nach Suva-Protokoll 66100-1 · Arbeitgeber: ${COMPANY.name} · erfasst am ${created}` };
  }
  if (cardHandover.value) {
    return { title: 'Unfall Fahrgast – Vormeldung', meta: 'Visitenkarte mit QR-Code abgegeben – der Fahrgast füllt die Meldung selbst aus.' };
  }
  if (s.type === 'passenger' || s.type === 'passenger_self') {
    return {
      title: 'Unfall- / Schadenmeldung Fahrgast',
      meta: `${selfReport.value ? 'Vom Fahrgast über den Link ausgefüllt' : 'Vor Ort mit dem Fahrgast ausgefüllt'} · erfasst am ${created}`,
    };
  }
  return {
    title: 'Unfallbericht / Schadenmeldung Motorfahrzeug',
    meta: `Versicherung: ${COMPANY.insurer} · Police ${COMPANY.policy} · Meldung erfasst am ${created}`,
  };
});

const holder = (vehicle) => (vehicle.owner ? `${vehicle.owner}, ${vehicle.owner_address}` : `${COMPANY.name}, ${COMPANY.address}`);

const photoNote = (front, back) => (front && back ? 'Fotos vorne und hinten im Anhang' : front ? 'Foto im Anhang' : '');

// Photos of the other party: in the QR flow they are already on the server (file names only)
const OTHER_PHOTO_NAMES = {
  licenceFront: 'Führerausweis Unfallbeteiligter vorne',
  licenceBack: 'Führerausweis Unfallbeteiligter hinten',
  registration: 'Fahrzeugausweis Unfallbeteiligter',
};
const otherPhoto = (key) => (usesQr(s)
  ? s.otherPartyPhotos.some((name) => name.startsWith(OTHER_PHOTO_NAMES[key]))
  : !!s.other[key]);

// Photo names as in the mail attachments («Foto 1.jpg», «Führerausweis ATE vorne.jpg» …)
const attachmentNames = computed(() => new Map(attachments(s).map((item) => [item.data, item.name])));
const photoItems = (photos) => photos.map((data) => ({ data, name: attachmentNames.value.get(data) || '' }));

const vehiclePhotos = computed(() => {
  const list = attachments(s);
  if (usesQr(s) && external.value) {
    // Insert the other party's server-side photos after our licence photos, as the prototype listed them
    const own = list.filter((item) => item.name.startsWith('Führerausweis ATE'));
    const rest = list.filter((item) => !item.name.startsWith('Führerausweis ATE'));
    return [...own, ...s.otherPartyPhotos.map((name) => ({ name, data: '' })), ...rest];
  }
  return list;
});

// ---------------------------------------------------------------- small render helpers

const SignatureImage = (props) => (props.src
  ? h('img', { src: props.src, alt: 'Unterschrift', style: 'max-height:70px;max-width:100%' })
  : '–');
SignatureImage.props = ['src'];

const WitnessRows = ({ witnesses, prefix }) => {
  const list = witnesses.filter((w) => w.name || w.phone);
  return list.length
    ? list.map((w, i) => h(ReportRow, { label: `${prefix} ${i + 1}`, value: `${w.name} · ${w.phone}` }))
    : h(ReportRow, { label: 'Zeugen', value: 'keine' });
};
WitnessRows.props = ['witnesses', 'prefix'];

const PassengerTripRows = ({ s: state, config: data }) => [
  h(ReportRow, { label: 'Datum / Zeit', value: `${formatDate(state.date)}, ${state.time} Uhr` }),
  h(ReportRow, { label: 'Linie / Richtung', value: lineText(state, data) }),
  needsControlCentre(state, data) ? h(ReportRow, { label: 'VBZ-Leitstelle', value: controlCentreText }) : null,
  h(ReportRow, { label: 'Haltestelle', value: stopName(state) }),
  h(ReportRow, { label: 'Wo genau', value: joinNote(state.where, state.whereNote) }),
  h(ReportRow, { label: 'Bus', value: busText(state) }),
];
PassengerTripRows.props = ['s', 'config'];
</script>
