<template>
  <div class="space-y-32 md:space-y-40">
    <!-- Alone: the other party fled or refused, note what is known -->
    <template v-if="isAlone(state) && state.aloneReason !== 'none'">
      <ReportCard
        title="Anderes Fahrzeug"
        hint="soweit bekannt"
        :subtitle="`${state.aloneReason === 'fled' ? 'Der Unfallbeteiligte ist geflüchtet.' : 'Der Unfallbeteiligte verweigert die Zusammenarbeit.'} Bitte alles notieren, was bekannt ist.`"
      >
        <OtherVehicleType />
        <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
          <FormInput v-model="state.other.plate" name="other_plate" label="Kennzeichen" placeholder="z. B. ZH 123 456" />
          <FormInput v-model="state.other.colour" name="other_colour" label="Farbe" placeholder="z. B. schwarz" />
        </FormGrid>
        <FormInput v-model="state.other.brand" name="other_brand" label="Marke / Typ" placeholder="z. B. VW Golf, Lieferwagen" />
        <FormInput v-model="state.other.driverDescription" name="other_driver" label="Beschreibung Lenker/in" placeholder="z. B. Mann, ca. 40, Fluchtrichtung Zürich" />
        <FormInput v-if="state.aloneReason === 'refused'" v-model="state.other.phone" name="other_phone" label="Handy Unfallbeteiligter (falls bekannt)" type="tel" />
        <NoticeBox tone="info">
          <p>Wenn möglich das Fahrzeug und das Kennzeichen fotografieren – im nächsten Schritt unter «Fotos» hochladen.</p>
        </NoticeBox>
      </ReportCard>
      <WitnessList />
    </template>

    <template v-else>
      <ReportCard v-if="!isAlone(state)" title="Welches Fahrzeug ist noch beteiligt?">
        <OptionButtons
          v-model="state.otherKind"
          name="other_kind"
          :columns="1"
          align="left"
          :options="[
            { value: 'external', label: 'Ein fremdes Fahrzeug' },
            { value: 'fleet', label: 'Ein zweites Fahrzeug der ATE Bus AG' },
            { value: 'none', label: 'Kein anderes Fahrzeug (Sachschaden)' },
          ]"
        />
        <p class="text-xs md:text-sm text-blue-gray">Sachschaden = z. B. geparktes Fahrzeug ohne Lenker/in, Gartenzaun, Mauer, Signal oder Haltestelle beschädigt.</p>
      </ReportCard>

      <!-- Other party on their own phone (QR code) -->
      <ReportCard
        v-if="usesQr(state)"
        title="Fahrzeug B: QR-Code scannen lassen"
        subtitle="Der Unfallbeteiligte scannt den Code mit seinem Handy und füllt seinen Teil selbst aus: Fahrzeugart, Ausweise, Kontakt, Manöver, Schaden, Aufprallpunkt und Unterschrift."
      >
        <QrCode v-if="state.otherPartyCode" :text="otherPartyUrl" />
        <NoticeBox v-else tone="warning"><p class="font-bold">QR-Code wird erstellt …</p></NoticeBox>

        <NoticeBox v-if="state.otherPartyDone" tone="success" title="✓ Fahrzeug B hat seinen Teil ausgefüllt">
          <p>{{ otherVehicleName(state.other.vehicleType) }} · {{ state.other.phone }} · {{ state.other.email }}</p>
        </NoticeBox>
        <NoticeBox v-else tone="warning"><p class="font-bold">Warten auf Fahrzeug B …</p></NoticeBox>

        <p class="text-xs md:text-sm text-blue-gray text-center">
          Scannen klappt nicht? Adresse von Hand eingeben: <b>{{ config.otherParty.manualUrl }}</b> · Code <b>{{ state.otherPartyCode || '…' }}</b>
        </p>

        <div v-if="!state.otherPartyDone" class="p-12 border-2 border-dashed border-[#D97706] bg-[#fffaf0] flex flex-col gap-y-8">
          <b>Funktioniert es nicht?</b>
          <span class="text-xs md:text-sm text-blue-gray">z. B. kein Internet, Akku leer, kein Smartphone oder der Unfallbeteiligte kommt nicht zurecht.</span>
          <button type="button" class="px-12 py-10 border-2 border-[#D97706] bg-white font-medium cursor-pointer" @click="state.fillMode = 'one'">
            Auf diesem Handy weiter ausfüllen
          </button>
        </div>
      </ReportCard>

      <!-- Other party on this phone -->
      <ReportCard v-if="state.otherKind === 'external' && !usesQr(state)" title="Unfallbeteiligter">
        <OtherVehicleType />
        <div class="grid grid-cols-2 gap-8 md:gap-12">
          <PhotoField v-model="state.other.licenceFront" label="Führerausweis Vorderseite" />
          <PhotoField v-model="state.other.licenceBack" label="Führerausweis Rückseite" />
        </div>
        <PhotoField v-model="state.other.registration" label="Fahrzeugausweis" />
        <FormInput v-model="state.other.phone" name="other_phone" label="Handy Unfallbeteiligter" type="tel" placeholder="079 123 45 67" />
        <OptionButtons
          v-model="state.other.ownerIsDriver"
          name="owner_is_driver"
          label="Ist der Unfallbeteiligte auch Halter des Fahrzeugs?"
          :options="[{ value: true, label: 'Ja' }, { value: false, label: 'Nein' }]"
        />
        <FormInput v-if="!state.other.ownerIsDriver" v-model="state.other.ownerPhone" name="owner_phone" label="Telefon Halter" type="tel" />
        <div>
          <FormInput v-model="state.other.email" name="other_email" label="E-Mail" type="email" placeholder="name@beispiel.ch" />
          <p class="mt-4 text-xs md:text-sm text-blue-gray">Erhält eine Kopie der Meldung.</p>
        </div>
      </ReportCard>

      <ReportCard v-if="state.otherKind === 'external'" title="Unsere Ausweise" subtitle="Der Unfallbeteiligte erhält die Fotos mit der Kopie der Meldung.">
        <div class="grid grid-cols-2 gap-8 md:gap-12">
          <PhotoField v-model="state.ownLicenceFront" label="Unser Führerausweis Vorderseite" />
          <PhotoField v-model="state.ownLicenceBack" label="Unser Führerausweis Rückseite" />
        </div>
        <p class="text-xs md:text-sm text-blue-gray">Die Fahrzeugdaten (Kontrollschild, Stammnummer, Halter, Versicherung) sind bereits hinterlegt – kein Foto des Fahrzeugausweises nötig.</p>
      </ReportCard>

      <!-- Second vehicle of our fleet -->
      <ReportCard v-if="state.otherKind === 'fleet'" title="Zweites Fahrzeug der ATE Bus AG">
        <VehiclePicker
          v-model="state.secondVehicleId"
          v-model:kind="state.secondVehicleKind"
          name="second_vehicle"
          :exclude="state.vehicleId"
          details="number"
        />
        <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
          <FormInput v-model="state.secondDriverLastName" name="second_driver_last_name" label="Mitarbeiter/in: Familienname" />
          <FormInput v-model="state.secondDriverFirstName" name="second_driver_first_name" label="Vorname" />
        </FormGrid>
        <p class="text-xs md:text-sm text-blue-gray">Interner Unfall: keine Ausweisfotos nötig, keine Kopie an Dritte.</p>
      </ReportCard>

      <!-- Damaged object, no other vehicle -->
      <ReportCard v-if="state.otherKind === 'none'" title="Was wurde beschädigt?">
        <ChipList v-model="state.damagedObjects" :options="DAMAGED_OBJECTS" />
        <FormTextarea v-model="state.damagedObjectNote" name="damaged_object_note" label="Ergänzung" placeholder="z. B. Kennzeichen geparktes Fahrzeug, Tafel, Pfosten" :rows="2" />
        <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
          <FormInput v-model="state.objectOwnerName" name="object_owner_name" label="Eigentümer (falls bekannt)" placeholder="Name" />
          <FormInput v-model="state.objectOwnerPhone" name="object_owner_phone" label="Telefon" type="tel" />
        </FormGrid>
        <FormInput v-model="state.objectOwnerEmail" name="object_owner_email" label="E-Mail (erhält eine Kopie)" type="email" />
      </ReportCard>

      <WitnessList v-if="state.otherKind" />
    </template>
  </div>
</template>

<script setup>
import { computed, h } from 'vue';
import FormInput from '../../form/FormInput.vue';
import FormTextarea from '../../form/FormTextarea.vue';
import FormGrid from '../../form/FormGrid.vue';
import ReportCard from '../ui/ReportCard.vue';
import OptionButtons from '../ui/OptionButtons.vue';
import ChipList from '../ui/ChipList.vue';
import NoticeBox from '../ui/NoticeBox.vue';
import PhotoField from '../ui/PhotoField.vue';
import QrCode from '../ui/QrCode.vue';
import VehiclePicker from '../ui/VehiclePicker.vue';
import WitnessList from '../ui/WitnessList.vue';
import { useAccidentReport } from '../context.js';
import { DAMAGED_OBJECTS, VEHICLE_TYPES, VEHICLE_TYPE_NAMES } from '../constants.js';
import { isAlone, usesQr } from '../state.js';
import { otherVehicleName } from '../report.js';

// Vehicle accident, step «Beteiligte»
const { config, state } = useAccidentReport();

const otherPartyUrl = computed(() => config.otherParty.pageUrl.replace('__CODE__', state.otherPartyCode));

const OtherVehicleType = () => h(OptionButtons, {
  modelValue: state.other.vehicleType,
  'onUpdate:modelValue': (value) => { state.other.vehicleType = value; },
  name: 'other_vehicle_type',
  label: 'Art des Fahrzeugs',
  columns: 3,
  size: 'sm',
  options: VEHICLE_TYPES.map((value) => ({ value, label: VEHICLE_TYPE_NAMES[value] })),
});
</script>
