import { useState, useEffect } from 'react';
import { UserSearchProps } from '../../types/user';
import { sanitizeSearchInput, isValidSearchTerm } from '../../utils/validators';
import { Search, X } from 'lucide-react';

export const UserSearch: React.FC<UserSearchProps> = ({
  value,
  onChange,
  placeholder = 'Search users by name...',
  isSearching = false,
}) => {
  const [localValue, setLocalValue] = useState(value);
  const [validationError, setValidationError] = useState<string | null>(null);
  const [isFocused, setIsFocused] = useState(false);

  useEffect(() => {
    setLocalValue(value);
  }, [value]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const rawValue = e.target.value;
    const sanitized = sanitizeSearchInput(rawValue);

    if (sanitized && !isValidSearchTerm(sanitized)) {
      setValidationError('Use letters, numbers, spaces, and .-\' only.');
      setLocalValue(sanitized);
      return;
    }

    setValidationError(null);
    setLocalValue(sanitized);
    onChange(sanitized);
  };

  const handleClear = () => {
    setLocalValue('');
    setValidationError(null);
    onChange('');
  };

  return (
    <div className="w-full max-w-md">
      <div className="relative">
        <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
          {isSearching ? (
            <div className="h-5 w-5 animate-spin rounded-full border-2 border-indigo-200 border-t-indigo-600" />
          ) : (
            <Search
              className={`h-5 w-5 transition-colors ${
                isFocused ? 'text-indigo-500' : 'text-gray-400'
              }`}
            />
          )}
        </div>
        <input
          type="text"
          value={localValue}
          onChange={handleChange}
          onFocus={() => setIsFocused(true)}
          onBlur={() => setIsFocused(false)}
          placeholder={placeholder}
          className={`w-full rounded-xl border-2 bg-white py-3 pl-12 pr-10 text-sm text-gray-700 placeholder-gray-400 transition-all focus:outline-none ${
            validationError
              ? 'border-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100'
              : 'border-gray-200 hover:border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100'
          }`}
          aria-label="Search users by name"
          aria-invalid={!!validationError}
          aria-describedby={validationError ? 'search-error' : undefined}
        />
        {localValue && (
          <button
            type="button"
            onClick={handleClear}
            className="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 transition-colors hover:text-gray-600"
            aria-label="Clear search"
          >
            <X className="h-5 w-5" />
          </button>
        )}
      </div>
      {validationError && (
        <p id="search-error" className="mt-2 text-sm text-red-500" role="alert">
          {validationError}
        </p>
      )}
    </div>
  );
};
