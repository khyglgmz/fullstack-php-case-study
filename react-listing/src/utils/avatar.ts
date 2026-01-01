export const AVATAR_COLORS = [
  'from-rose-400 to-pink-500',
  'from-orange-400 to-amber-500',
  'from-emerald-400 to-teal-500',
  'from-cyan-400 to-blue-500',
  'from-indigo-400 to-purple-500',
  'from-violet-400 to-fuchsia-500',
];

export const getAvatarColor = (id: number): string => {
  return AVATAR_COLORS[id % AVATAR_COLORS.length];
};

export const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();
};
