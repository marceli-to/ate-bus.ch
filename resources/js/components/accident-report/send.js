import { createApp } from 'vue';
import axios from 'axios';
import AccidentReportDocument from './report/AccidentReportDocument.vue';
import { attachments, copyTo, subject } from './report.js';
import { submitType, usesQr } from './state.js';
import { IMPACT_HEIGHT, IMPACT_WIDTH, impactSvg, sketchSvg, svgToPng } from './svg.js';
import { dataUrlToBlob } from './photos.js';

const impactPng = (impact) => (impact
  ? svgToPng(impactSvg([impact.type], impact, { readonly: true }), IMPACT_WIDTH, IMPACT_HEIGHT)
  : null);

/** Render the full report as mail HTML (drawings as PNG, photos as attachment names) */
async function mailReport(state, config) {
  const sketchVehicles = {
    busA: state.vehicleKind === 'bus',
    busB: state.otherKind === 'fleet' && state.secondVehicleKind === 'bus',
  };
  const [impactA, impactB, sketch] = await Promise.all([
    impactPng(state.impactA),
    impactPng(state.impactB),
    state.type === 'vehicle' && state.sketch.elements.length ? svgToPng(sketchSvg(state.sketch, sketchVehicles), 300, 300) : null,
  ]);

  const el = document.createElement('div');
  const app = createApp(AccidentReportDocument, { state, config, full: true, mail: { impactA, impactB, sketch } });
  app.mount(el);
  const html = el.innerHTML;
  app.unmount();

  return { html, sketch };
}

/** Send the report. Resolves with the server answer { success, mail_sent }, throws with a German message. */
export async function sendReport(state, config) {
  const { html, sketch } = await mailReport(state, config);
  const files = attachments(state);
  if (sketch) files.push({ name: 'Unfallskizze.png', data: sketch });

  const data = new FormData();
  data.append('type', submitType(state));
  data.append('subject', subject(state, config));
  data.append('copy_to', copyTo(state) || '');
  data.append('report', html);
  data.append('website', '');
  if (usesQr(state) && state.otherPartyCode) data.append('other_party_code', state.otherPartyCode);
  files.forEach((file) => data.append('files[]', dataUrlToBlob(file.data), file.name));

  try {
    const response = await axios.post(config.submitUrl, data, { headers: { Accept: 'application/json' } });
    return response.data;
  } catch (error) {
    throw new Error(errorMessage(error));
  }
}

export function errorMessage(error) {
  const response = error.response;
  if (!response) return 'Keine Verbindung.';
  if (response.status === 422) return [...new Set(Object.values(response.data?.errors || {}).flat())].join(' ') || 'Angaben unvollständig.';
  if (response.status === 413) return 'Die Fotos sind zusammen zu gross.';
  if (response.status === 429) return 'Zu viele Meldungen in kurzer Zeit. Bitte in ein paar Minuten nochmals versuchen.';
  if (response.status === 404) return response.data?.message || 'Der Link ist nicht mehr gültig.';
  return response.data?.message || `Fehler ${response.status}`;
}
