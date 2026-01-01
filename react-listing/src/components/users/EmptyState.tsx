import { EmptyStateProps } from '../../types/user';
import { IconContainer } from '../common/IconContainer';
import { User } from 'lucide-react';

export const EmptyState: React.FC<EmptyStateProps> = ({
  message = 'No users to show',
}) => {
  return (
    <div className="flex flex-col items-center justify-center py-20">
      <div className="mb-4">
        <IconContainer size="md" bgColor="bg-gray-100" textColor="text-gray-400">
          <User className="h-8 w-8" strokeWidth={1.5} />
        </IconContainer>
      </div>
      <p className="text-base font-medium text-gray-600">{message}</p>
      <p className="mt-1 text-sm text-gray-400">Try adjusting the search</p>
    </div>
  );
};
