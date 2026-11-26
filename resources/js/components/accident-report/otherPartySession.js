import { onBeforeUnmount, onMounted, watch } from 'vue';
import axios from 'axios';
import { usesQr } from './state.js';
import { vehicleA } from './report.js';

const POLL_MS = 3000;

/**
 * Driver's phone, «each on their own phone»: create the code for the QR code, then poll every
 * 3 seconds until the other party has sent their part, and take it over into the report.
 */
export function useOtherPartySession(state, config) {
  let busy = false;
  let timer = null;

  const active = () => config.mode === 'staff' && usesQr(state) && !state.otherPartyDone;

  const take = ({ data, photos }) => {
    // Vehicle type first: changing it clears the impact point
    state.other.vehicleType = data.vehicleType;
    Object.assign(state.other, {
      phone: data.phone,
      ownerIsDriver: data.ownerIsDriver,
      ownerPhone: data.ownerPhone,
      email: data.email,
    });
    state.maneuversB = data.maneuvers;
    state.damagesB = data.damages;
    state.damageNoteB = data.damageNote;
    state.impactB = data.impact;
    state.signatureB = data.signature;
    state.otherPartyPhotos = photos;
    state.otherPartyDone = true;
  };

  const tick = async () => {
    if (busy || !active()) return;
    busy = true;
    try {
      if (!state.otherPartyCode) {
        const { data } = await axios.post(config.otherParty.startUrl, {
          date: state.date,
          time: state.time,
          vehicle: vehicleA(state, config)?.label || '',
        });
        state.otherPartyCode = data.code;
      } else {
        const { data } = await axios.get(config.otherParty.statusUrl.replace('__CODE__', state.otherPartyCode));
        if (data.done) take(data);
      }
    } catch (error) {
      // Code expired (404): start a new one on the next tick. Network errors: just try again.
      if (error.response?.status === 404) state.otherPartyCode = '';
    } finally {
      busy = false;
    }
  };

  onMounted(() => {
    timer = setInterval(tick, POLL_MS);
  });
  onBeforeUnmount(() => clearInterval(timer));

  // Start right away when the QR step opens
  watch(() => usesQr(state), (on) => on && tick());
}
