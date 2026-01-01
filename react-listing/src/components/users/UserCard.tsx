import { memo } from 'react';
import { UserCardProps } from '../../types/user';
import { Avatar } from '../common/Avatar';
import { Mail, ChevronRight } from 'lucide-react';
import { KEYBOARD_KEYS } from '../../utils/constants';

export const UserCard = memo<UserCardProps>(({ user, onClick }) => {
  const handleClick = () => onClick(user);

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === KEYBOARD_KEYS.ENTER || e.key === KEYBOARD_KEYS.SPACE) {
      e.preventDefault();
      onClick(user);
    }
  };

  return (
    <article
      className="group relative cursor-pointer overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      onClick={handleClick}
      onKeyDown={handleKeyDown}
      tabIndex={0}
      role="button"
      aria-label={`View details for ${user.name}`}
    >
      <div className="flex items-start gap-4">
        <Avatar name={user.name} id={user.id} size="md" />
        <div className="min-w-0 flex-1">
          <h2 className="truncate text-base font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
            {user.name}
          </h2>
          <p className="mt-1 flex items-center gap-1.5 truncate text-sm text-gray-500">
            <Mail className="h-3.5 w-3.5 flex-shrink-0 text-gray-400" />
            <span className="truncate">{user.email.toLowerCase()}</span>
          </p>
          <div className="mt-3">
            <span className="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
              {user.company.name}
            </span>
          </div>
        </div>
      </div>
      <div className="absolute right-4 top-1/2 -translate-y-1/2 opacity-0 transition-opacity group-hover:opacity-100">
        <ChevronRight className="h-5 w-5 text-indigo-400" />
      </div>
    </article>
  );
});

UserCard.displayName = 'UserCard';
