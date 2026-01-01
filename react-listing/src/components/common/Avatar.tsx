import { getAvatarColor, getInitials } from '../../utils/avatar';

interface AvatarProps {
  name: string;
  id: number;
  size?: 'sm' | 'md' | 'lg';
}

const sizeClasses = {
  sm: 'h-10 w-10 text-sm rounded-xl',
  md: 'h-12 w-12 text-sm rounded-xl',
  lg: 'h-20 w-20 text-2xl rounded-2xl',
};

export const Avatar: React.FC<AvatarProps> = ({ name, id, size = 'md' }) => {
  const initials = getInitials(name);
  const color = getAvatarColor(id);

  return (
    <div
      className={`flex items-center justify-center bg-gradient-to-br ${color} font-semibold text-white shadow-sm ${sizeClasses[size]}`}
    >
      {initials}
    </div>
  );
};
