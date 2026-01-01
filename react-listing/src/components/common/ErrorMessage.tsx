import { ErrorMessageProps } from '../../types/user';
import { IconContainer } from './IconContainer';
import { AlertTriangle, RefreshCw } from 'lucide-react';

export const ErrorMessage: React.FC<ErrorMessageProps> = ({
  message,
  onRetry,
}) => {
  return (
    <div
      className="mx-auto flex max-w-md flex-col items-center justify-center py-16 text-center"
      role="alert"
    >
      <div className="mb-4">
        <IconContainer size="md" bgColor="bg-red-100" textColor="text-red-500">
          <AlertTriangle className="h-8 w-8" strokeWidth={1.5} />
        </IconContainer>
      </div>
      <p className="text-base font-medium text-gray-900">Couldn’t load users</p>
      <p className="mt-1 text-sm text-gray-500">{message}</p>
      {onRetry && (
        <button
          onClick={onRetry}
          className="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          <RefreshCw className="h-4 w-4" />
          Retry
        </button>
      )}
    </div>
  );
};
