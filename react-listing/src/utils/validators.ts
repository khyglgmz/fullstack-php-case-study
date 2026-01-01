import { MAX_SEARCH_LENGTH } from './constants';

export const sanitizeSearchInput = (input: string): string => {
  return input
    .trim()
    .replace(/[<>]/g, '')
    .slice(0, MAX_SEARCH_LENGTH);
};

export const isValidSearchTerm = (term: string): boolean => {
  const pattern = /^[a-zA-Z0-9\s\-'.]*$/;
  return pattern.test(term);
};
