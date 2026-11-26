<template>
  <div class="space-y-32 md:space-y-40">
    <ReportCard title="Gibt es Verletzte?">
      <OptionButtons
        v-model="state.injured"
        name="injured"
        size="lg"
        :options="[{ value: true, label: 'Ja', tone: 'danger' }, { value: false, label: 'Nein', tone: 'safe' }]"
      />
    </ReportCard>

    <NoticeBox v-if="state.injured === true" tone="danger" title="Sofort Polizei aufbieten">
      <p class="text-2xl font-bold text-error">☎ <PhoneLink :number="PHONE.police" /></p>
      <p>Bei Verletzten: Sanität <PhoneLink :number="PHONE.ambulance" />.</p>
      <EmergencyCalls urgent />
      <p><b>Dieses Formular wird nicht ausgefüllt.</b> Die Polizei nimmt den Unfall auf.</p>
    </NoticeBox>

    <template v-if="state.injured === false">
      <ReportCard title="Füllen Sie das Formular zusammen mit dem Unfallbeteiligten aus?">
        <OptionButtons
          v-model="state.together"
          name="together"
          :columns="1"
          :options="[
            { value: 'together', label: 'Ja – wir machen das zusammen', description: 'Mitarbeiter/in und Unfallbeteiligter füllen das Formular gemeinsam aus.' },
            { value: 'alone', label: 'Nein – ich mache das alleine', description: 'Nur unsere Seite ausfüllen, wenn der Unfallbeteiligte geflüchtet ist, die Zusammenarbeit verweigert oder kein anderes Fahrzeug beteiligt ist (Zaun, Pfahl, geparktes Auto usw.).' },
          ]"
        />
      </ReportCard>

      <!-- Alone: other party fled, refused, or no other vehicle -->
      <template v-if="state.together === 'alone'">
        <ReportCard title="Warum alleine?">
          <OptionButtons
            v-model="state.aloneReason"
            name="alone_reason"
            :columns="1"
            align="left"
            :options="[
              { value: 'fled', label: 'Unfallbeteiligter ist geflüchtet' },
              { value: 'refused', label: 'Unfallbeteiligter verweigert die Zusammenarbeit' },
              { value: 'none', label: 'Kein anderes Fahrzeug beteiligt (Zaun, Pfahl, geparktes Auto …)' },
            ]"
          />
        </ReportCard>

        <NoticeBox v-if="state.aloneReason === 'fled' || state.aloneReason === 'refused'" tone="danger">
          <h3 class="font-bold text-md md:text-lg text-error">Polizei <PhoneLink :number="PHONE.police" /> informieren</h3>
          <p v-if="state.aloneReason === 'fled'">Fahrerflucht: <b>Kennzeichen, Marke, Farbe</b> und Fluchtrichtung merken und der Polizei melden.</p>
          <p v-else>Der Unfallbeteiligte verweigert die Zusammenarbeit: die Polizei aufbieten, damit der Unfall aufgenommen wird.</p>
          <EmergencyCalls />
          <CheckField v-model="state.policeInformed" label="Polizei ist informiert" />
        </NoticeBox>

        <NoticeBox v-if="state.aloneReason" tone="info" title="Wie geht es weiter?">
          <p>
            Sie füllen nur <b>unsere Seite</b> aus. Die Meldung geht <b>per E-Mail an die ATE Bus AG</b>{{ state.aloneReason === 'none' ? ' (und an den Eigentümer, falls E-Mail bekannt)' : '' }}.
          </p>
        </NoticeBox>
      </template>

      <!-- Together -->
      <template v-if="state.together === 'together'">
        <ReportCard title="Ist die Schuldfrage geklärt?" subtitle="Beide Parteien sind sich vor Ort einig, wer den Unfall verursacht hat. Bei reinem Sachschaden ohne fremdes Fahrzeug (z. B. Zaun) in der Regel «Ja».">
          <OptionButtons
            v-model="state.faultAgreed"
            name="fault_agreed"
            size="lg"
            :options="[{ value: true, label: 'Ja', tone: 'safe' }, { value: false, label: 'Nein', tone: 'danger' }]"
          />
        </ReportCard>

        <NoticeBox v-if="state.faultAgreed === false" tone="danger" title="Polizei aufbieten">
          <p class="text-2xl font-bold text-error">☎ <PhoneLink :number="PHONE.police" /></p>
          <p>Die Schuldfrage ist nicht geklärt – die Polizei nimmt den Unfall auf.</p>
          <EmergencyCalls />
          <p><b>Dieses Formular wird nicht ausgefüllt.</b> Keine Schuld anerkennen und nichts unterschreiben.</p>
        </NoticeBox>

        <template v-if="state.faultAgreed === true">
          <ReportCard title="Möchte der Unfallbeteiligte unser digitales Formular ausfüllen?">
            <div class="grid grid-cols-2 gap-8 text-xs md:text-sm">
              <div class="p-10 bg-success/10 border border-success space-y-4">
                <b class="block text-success">✓ Digitales Formular</b>
                <span>schneller · einfach und intuitiv · gut lesbar · Kopie per E-Mail</span>
              </div>
              <div class="p-10 bg-light-grey border border-dark-grey space-y-4">
                <b class="block text-blue-gray">✗ Europäisches Unfallprotokoll</b>
                <span>braucht mehr Zeit · meist schlecht leserlich · kompliziert</span>
              </div>
            </div>
            <OptionButtons
              v-model="state.digital"
              name="digital"
              :options="[{ value: true, label: 'Ja – digitales Formular', tone: 'safe' }, { value: false, label: 'Nein – Europäisches Protokoll', tone: 'danger' }]"
            />
            <p class="text-xs md:text-sm text-blue-gray">Ohne fremden Unfallbeteiligten (z. B. Gartenzaun, geparktes Fahrzeug, zwei ATE-Fahrzeuge): «Ja» wählen.</p>
          </ReportCard>

          <NoticeBox v-if="state.digital === false" tone="info" title="Europäisches Unfallprotokoll ausfüllen">
            <p>Das Europäische Unfallprotokoll zusammen mit dem Unfallbeteiligten ausfüllen.</p>
            <p><b>Der Prozess ist hier fertig</b> – dieses Formular wird nicht weiter ausgefüllt.</p>
          </NoticeBox>

          <template v-if="state.digital === true">
            <NoticeBox tone="success" title="Wie geht es weiter?">
              <p><b>Dieses Formular ersetzt das Europäische Unfallprotokoll.</b></p>
              <p>Das Formular wird nach dem Ausfüllen <b>per E-Mail an die ATE Bus AG</b> und <b>an den Unfallbeteiligten</b> (fremde Partei) verschickt.</p>
              <p>Bitte den Unfallbeteiligten darauf hinweisen – dafür braucht es seine <b>E-Mail-Adresse</b>.</p>
            </NoticeBox>

            <ReportCard title="Wie füllen Sie zusammen aus?">
              <OptionButtons
                v-model="state.fillMode"
                name="fill_mode"
                :columns="1"
                :options="[
                  { value: 'one', label: 'Auf einem Handy', description: 'Der Unfallbeteiligte gibt seine Angaben auf Ihrem Handy ein.' },
                  { value: 'qr', label: 'Jeder auf seinem Handy', description: 'Der Unfallbeteiligte scannt einen QR-Code und füllt seinen Teil gleichzeitig auf seinem eigenen Handy aus.' },
                ]"
              />
              <p class="text-xs md:text-sm text-blue-gray">Der QR-Code erscheint im Schritt «Beteiligte», wenn ein fremdes Fahrzeug beteiligt ist.</p>
            </ReportCard>
          </template>
        </template>
      </template>
    </template>
  </div>
</template>

<script setup>
import ReportCard from '../ui/ReportCard.vue';
import OptionButtons from '../ui/OptionButtons.vue';
import NoticeBox from '../ui/NoticeBox.vue';
import PhoneLink from '../ui/PhoneLink.vue';
import CheckField from '../ui/CheckField.vue';
import EmergencyCalls from '../ui/EmergencyCalls.vue';
import { useAccidentReport } from '../context.js';
import { PHONE } from '../constants.js';

// Vehicle accident, step «Ausschluss»: when the form must not be used (injured, fault unclear, paper protocol)
const { state } = useAccidentReport();
</script>
