// Drawings of the accident report (from the approved prototype), as SVG strings with
// presentation attributes only: the same markup is shown on screen and turned into PNG for the mail.

import { VEHICLE_TYPE_NAMES } from './constants.js';

const BLUE = '#1355A1';
const RED = '#C62828';

// ---------------------------------------------------------------- first point of impact

// Vehicles seen from above, front at the top, 120 × 222 each
const VEHICLE_SHAPES = {
  bus: '<rect x="22" y="26" width="76" height="186" rx="9"/><path d="M27 34h66v14H27z"/><path d="M98 60v26M98 140v26" stroke-width="3"/><rect x="45" y="80" width="30" height="16" rx="2"/><rect x="45" y="150" width="30" height="16" rx="2"/><path d="M60 200v8"/><path d="M18 40h4M98 40h4"/>',
  car: '<rect x="30" y="30" width="60" height="170" rx="26"/><path d="M38 86q22-12 44 0l-4 22q-18-6-36 0z"/><path d="M42 168q18 5 36 0l3 16q-21 6-42 0z"/><path d="M40 110v52M80 110v52"/><path d="M24 96h6M90 96h6"/><path d="M38 40q22-10 44 0"/>',
  van: '<rect x="26" y="28" width="68" height="182" rx="12"/><path d="M32 66q28-10 56 0l-2 20q-26-6-52 0z"/><path d="M46 96v100M74 96v100"/><path d="M20 76h6M94 76h6"/><path d="M50 204h20"/><path d="M34 40q26-10 52 0"/>',
  truck: '<rect x="30" y="26" width="60" height="40" rx="7"/><path d="M35 34q25-8 50 0l-2 12q-23-5-46 0z"/><path d="M24 40h6M90 40h6"/><rect x="22" y="70" width="76" height="142" rx="3"/><path d="M22 108h76M22 146h76M22 184h76"/>',
  bicycle: '<ellipse cx="60" cy="58" rx="4" ry="20"/><ellipse cx="60" cy="176" rx="4" ry="20"/><path d="M60 78v78M42 74l18 6 18-6"/><ellipse cx="60" cy="140" rx="5" ry="9"/>',
  motorcycle: '<ellipse cx="60" cy="52" rx="7" ry="16"/><path d="M36 74l14-6h20l14 6"/><circle cx="60" cy="78" r="6"/><path d="M50 84q10-4 20 0l-2 54q-8 6-16 0z"/><ellipse cx="60" cy="150" rx="9" ry="22"/><ellipse cx="60" cy="190" rx="7" ry="16"/>',
};

export const IMPACT_WIDTH = 120;
export const IMPACT_HEIGHT = 222;

/**
 * @param {string[]} types  vehicle types side by side
 * @param {{type: string, x: number, y: number}|null} selection
 */
export function impactSvg(types, selection, { readonly = false } = {}) {
  const width = types.length * IMPACT_WIDTH;
  const vehicles = types.map((type, i) => {
    const dimmed = selection && selection.type !== type && !readonly;
    return `<g transform="translate(${i * IMPACT_WIDTH},0)">`
      + `<path d="M60 4v14M54 10l6-7 6 7" fill="none" stroke="${BLUE}" stroke-width="2"/>`
      + `<text x="68" y="13" font-size="7" fill="${BLUE}" font-family="Arial, sans-serif">Fahrtrichtung</text>`
      + `<g fill="#fff" stroke="${dimmed ? '#bbb' : '#555'}" stroke-width="1.6">${VEHICLE_SHAPES[type]}</g>`
      + `<text x="60" y="220" font-size="10" text-anchor="middle" fill="#555" font-family="Arial, sans-serif">${VEHICLE_TYPE_NAMES[type]}</text>`
      + '</g>';
  }).join('');

  const index = selection ? types.indexOf(selection.type) : -1;
  const mark = index >= 0
    ? `<g transform="translate(${index * IMPACT_WIDTH + selection.x},${selection.y})"><path d="M-9-9L9 9M9-9L-9 9" stroke="${RED}" stroke-width="4" stroke-linecap="round"/></g>`
    : '';

  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width} ${IMPACT_HEIGHT}" width="${width}" height="${IMPACT_HEIGHT}">${vehicles}${mark}</svg>`;
}

/** Point tapped on the impact sketch, or null outside a vehicle */
export function impactPointFromEvent(event, svg, types) {
  const point = svg.createSVGPoint();
  point.x = event.clientX;
  point.y = event.clientY;
  const p = point.matrixTransform(svg.getScreenCTM().inverse());
  const i = Math.max(0, Math.min(types.length - 1, Math.floor(p.x / IMPACT_WIDTH)));
  if (p.y < 24 || p.y > 214) return null;
  return { type: types[i], x: Math.round(p.x - i * IMPACT_WIDTH), y: Math.round(p.y) };
}

// ---------------------------------------------------------------- accident sketch (300 × 300)

export const SKETCH_BACKGROUNDS = {
  empty: { label: 'Leer', svg: '<rect width="300" height="300" fill="#fff"/>' },
  street: {
    label: 'Strasse',
    svg: '<rect width="300" height="300" fill="#e5ebe0"/><rect x="95" width="110" height="300" fill="#6b7280"/><path d="M99 0v300M201 0v300" stroke="#fff" stroke-width="2"/><path d="M150 0v300" stroke="#fff" stroke-width="3" stroke-dasharray="14 12"/>',
  },
  junction: {
    label: 'Kreuzung',
    svg: '<rect width="300" height="300" fill="#e5ebe0"/><rect x="95" width="110" height="300" fill="#6b7280"/><rect y="95" width="300" height="110" fill="#6b7280"/><path d="M150 0v88M150 212v88M0 150h88M212 150h88" stroke="#fff" stroke-width="3" stroke-dasharray="14 12"/><path d="M99 0v95M201 0v95M99 205v95M201 205v95M0 99h95M0 201h95M205 99h95M205 201h95" stroke="#fff" stroke-width="2"/>',
  },
  roundabout: {
    label: 'Kreisel',
    svg: '<rect width="300" height="300" fill="#e5ebe0"/><rect x="110" width="80" height="300" fill="#6b7280"/><rect y="110" width="300" height="80" fill="#6b7280"/><circle cx="150" cy="150" r="80" fill="#6b7280"/><circle cx="150" cy="150" r="36" fill="#9cbf8a" stroke="#fff" stroke-width="3"/>',
  },
  car_park: {
    label: 'Parkplatz',
    svg: '<rect width="300" height="300" fill="#7b8390"/>'
      + [0, 1, 2, 3, 4, 5].map((i) => `<path d="M${20 + i * 45} 20v70M${20 + i * 45} 210v70" stroke="#fff" stroke-width="2"/>`).join('')
      + '<path d="M20 90h225M20 210h225" stroke="#fff" stroke-width="2"/>',
  },
};

const sketchVehicle = (label, colour, isBus) => {
  const h = isBus ? 70 : 46;
  const w = isBus ? 26 : 24;
  return `<rect x="${-w / 2}" y="${-h / 2}" width="${w}" height="${h}" rx="6" fill="${colour}" stroke="#111" stroke-width="2"/>`
    + `<rect x="${-w / 2 + 4}" y="${-h / 2 + 6}" width="${w - 8}" height="7" rx="2" fill="#dbe6f5"/>`
    + `<rect x="${-w / 2 + 4}" y="${h / 2 - 12}" width="${w - 8}" height="6" rx="2" fill="#dbe6f5"/>`
    + `<path d="M-6 ${-h / 2 + 1}l6-6 6 6z" fill="#111"/>`
    + `<text y="5" text-anchor="middle" font-size="14" font-weight="800" fill="#fff" font-family="Arial, sans-serif">${label}</text>`;
};

/** Elements of the sketch; A and B depend on the vehicles of the report */
export const SKETCH_ELEMENTS = {
  A: { label: 'Fahrzeug A', svg: ({ busA }) => sketchVehicle('A', '#1f5fd6', busA) },
  B: { label: 'Fahrzeug B', svg: ({ busB }) => sketchVehicle('B', '#16a34a', busB) },
  arrow: { label: 'Pfeil', svg: () => '<path d="M-5 26v-30h-9l14-22 14 22h-9v30z" fill="#dc2626" stroke="#7f1d1d" stroke-width="1.5"/>' },
  stop_sign: { label: 'Stopp', svg: () => '<path d="M-7-17h14l10 10v14l-10 10h-14l-10-10v-14z" fill="#dc2626" stroke="#fff" stroke-width="1.5"/><text y="4" text-anchor="middle" font-size="8" font-weight="800" fill="#fff" font-family="Arial, sans-serif">STOP</text>' },
  give_way: { label: 'Kein Vortritt', svg: () => '<path d="M-16-12h32l-16 28z" fill="#fff" stroke="#dc2626" stroke-width="5" stroke-linejoin="round"/>' },
  traffic_light: { label: 'Ampel', svg: () => '<rect x="-7" y="-18" width="14" height="36" rx="3" fill="#111"/><circle cy="-10" r="4" fill="#ef4444"/><circle r="4" fill="#facc15"/><circle cy="10" r="4" fill="#22c55e"/>' },
  pedestrian: { label: 'Fussgänger', svg: () => '<rect x="-13" y="-13" width="26" height="26" rx="3" fill="#1d4ed8"/><circle cy="-7" r="2.6" fill="#fff"/><path d="M0-4v8M0 4l-4 6M0 4l4 6M-4-1h8" stroke="#fff" stroke-width="2" stroke-linecap="round"/>' },
};

export const elementSvg = (type, vehicles) => SKETCH_ELEMENTS[type].svg(vehicles);

/**
 * @param {{background: string, elements: {type: string, x: number, y: number, rotation: number, scale: number}[]}} sketch
 * @param {{busA: boolean, busB: boolean}} vehicles
 */
export function sketchSvg(sketch, vehicles, { selected = -1, editable = false } = {}) {
  const elements = sketch.elements.map((element, i) => {
    const frame = editable && i === selected
      ? '<rect x="-30" y="-40" width="60" height="80" fill="none" stroke="#2563eb" stroke-dasharray="4 3"/>'
      : '';
    const handle = editable ? ` data-index="${i}" style="cursor:grab"` : '';
    return `<g transform="translate(${element.x} ${element.y}) rotate(${element.rotation}) scale(${element.scale})"${handle}>${elementSvg(element.type, vehicles)}${frame}</g>`;
  }).join('');

  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300" width="300" height="300">${SKETCH_BACKGROUNDS[sketch.background].svg}${elements}</svg>`;
}

// ---------------------------------------------------------------- body chart (right / left as seen by the injured person)

const rect = (x, y, w, h, r = 5) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}"/>`;
const ellipse = (x, y, rx, ry) => `<ellipse cx="${x}" cy="${y}" rx="${rx}" ry="${ry}"/>`;

/** @returns {[string, string][]}  [name, shape] */
export function bodyParts(back) {
  const view = back ? 'hinten' : 'vorne';
  // Front view: the person's right side is on the left of the picture
  const side = (x) => (x < 60 ? (back ? 'links' : 'rechts') : (back ? 'rechts' : 'links'));
  const parts = [];
  const add = (name, shape, x) => parts.push([`${name}${x !== undefined ? ` ${side(x)}` : ''}${name.includes('(') ? '' : ` (${view})`}`, shape]);

  add(back ? 'Hinterkopf' : 'Kopf / Gesicht', ellipse(60, 22, 14, 17));
  add(back ? 'Nacken' : 'Hals', rect(53, 38, 14, 10, 3));
  [38, 82].forEach((x) => add('Schulter', ellipse(x, 54, 10, 8), x));
  add(back ? 'Oberer Rücken' : 'Brust / Rippen', rect(44, 48, 32, 30, 6));
  add(back ? 'Unterer Rücken' : 'Bauch', rect(45, 78, 30, 26, 4));
  add(back ? 'Gesäss' : 'Hüfte / Leiste', rect(44, 104, 32, 18, 6));
  [26, 83].forEach((x) => add('Oberarm', rect(x, 60, 11, 30), x));
  [31, 89].forEach((x) => add('Ellbogen', ellipse(x, 93, 6, 6), x));
  [23, 86].forEach((x) => add('Unterarm', rect(x, 98, 11, 28), x));
  [27, 93].forEach((x) => add(back ? 'Handrücken' : 'Hand / Finger', ellipse(x, 134, 7, 9), x));
  [45, 61].forEach((x) => add('Oberschenkel', rect(x, 122, 14, 46, 6), x));
  [52, 68].forEach((x) => add(back ? 'Kniekehle' : 'Knie', ellipse(x, 172, 7, 6), x));
  [46, 62].forEach((x) => add(back ? 'Wade' : 'Unterschenkel', rect(x, 178, 12, 48), x));
  [51, 69].forEach((x) => add(back ? 'Ferse' : 'Fuss / Knöchel', ellipse(x, 234, 8, 6), x));

  return parts;
}

export const FACE_PARTS = [
  ['Stirn', '<path d="M30 46q30-36 60 0v6H30z"/>'],
  ['Auge rechts', '<ellipse cx="45" cy="62" rx="10" ry="6"/>'],
  ['Auge links', '<ellipse cx="75" cy="62" rx="10" ry="6"/>'],
  ['Nase', '<path d="M60 64l-7 24h14z"/>'],
  ['Wange rechts', '<ellipse cx="40" cy="86" rx="9" ry="10"/>'],
  ['Wange links', '<ellipse cx="80" cy="86" rx="9" ry="10"/>'],
  ['Mund / Zähne', '<rect x="47" y="96" width="26" height="10" rx="5"/>'],
  ['Kinn / Kiefer', '<path d="M38 108q22 26 44 0v4q-22 22-44 0z"/>'],
  ['Ohr rechts', '<ellipse cx="22" cy="72" rx="6" ry="12"/>'],
  ['Ohr links', '<ellipse cx="98" cy="72" rx="6" ry="12"/>'],
].map(([name, shape]) => [`Gesicht: ${name}`, shape]);

export const FACE_HINT_FRONT = '<path d="M52 18q3-2 6 0M62 18q3-2 6 0"/><circle cx="55" cy="20.5" r="1.3" fill="#1355A1" stroke="none"/><circle cx="65" cy="20.5" r="1.3" fill="#1355A1" stroke="none"/><path d="M60 21v6l-2 1"/><path d="M55 32q5 3 10 0"/>';
export const FACE_HINT_BACK = '<path d="M47 18q13-16 26 0"/><path d="M49 26q11-6 22 0"/><path d="M52 33q8 3 16 0"/>';

// ---------------------------------------------------------------- PNG for mail programs (Outlook shows no SVG)

/** @returns {Promise<string|null>} PNG data URL */
export function svgToPng(svg, width, height, scale = 2) {
  return new Promise((resolve) => {
    const image = new Image();
    image.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = width * scale;
      canvas.height = height * scale;
      const context = canvas.getContext('2d');
      context.fillStyle = '#fff';
      context.fillRect(0, 0, canvas.width, canvas.height);
      context.drawImage(image, 0, 0, canvas.width, canvas.height);
      resolve(canvas.toDataURL('image/png'));
    };
    image.onerror = () => resolve(null);
    image.src = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}`;
  });
}
