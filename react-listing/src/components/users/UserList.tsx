import { UserListProps } from '../../types/user';
import { Spinner } from '../common/Spinner';
import { ErrorMessage } from '../common/ErrorMessage';
import { EmptyState } from './EmptyState';
import { UserCard } from './UserCard';

export const UserList: React.FC<UserListProps> = ({
  users,
  isLoading,
  error,
  onUserClick,
  onRetry,
}) => {
  if (isLoading) {
    return <Spinner />;
  }

  if (error) {
    return <ErrorMessage message={error} onRetry={onRetry} />;
  }

  if (users.length === 0) {
    return <EmptyState message="No users to show" />;
  }

  return (
    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      {users.map((user) => (
        <UserCard key={user.id} user={user} onClick={onUserClick} />
      ))}
    </div>
  );
};
