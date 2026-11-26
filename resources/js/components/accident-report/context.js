import { inject, provide } from 'vue';

const KEY = Symbol('accidentReport');

/**
 * @param {object} config  from the page: { mode, vehicles, lines, recipients, submitUrl, otherParty }
 * @param {object} state   from createState()
 */
export function provideAccidentReport(config, state) {
  provide(KEY, { config, state });
}

/** @returns {{config: object, state: object}} */
export function useAccidentReport() {
  return inject(KEY);
}
