// API Configuration
export const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || 'https://jsonplaceholder.typicode.com';

export const API_TIMEOUT =
  Number(import.meta.env.VITE_API_TIMEOUT) || 10000;

export const DEBOUNCE_DELAY = 300;

export const MAX_SEARCH_LENGTH = 100;

export const KEYBOARD_KEYS = {
  ENTER: 'Enter',
  SPACE: ' ',
  ESCAPE: 'Escape',
} as const;

export const Z_INDEX = {
  MODAL: 50,
  DROPDOWN: 40,
  TOOLTIP: 30,
} as const;

export const ANIMATION = {
  MODAL_CLOSE_DELAY: 200,
} as const;
