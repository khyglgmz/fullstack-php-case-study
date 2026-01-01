interface IconContainerProps {
  children: React.ReactNode;
  size?: 'sm' | 'md' | 'lg';
  bgColor?: string;
  textColor?: string;
}

const sizeClasses = {
  sm: 'h-10 w-10 rounded-lg',
  md: 'h-16 w-16 rounded-2xl',
  lg: 'h-20 w-20 rounded-2xl',
};

export const IconContainer: React.FC<IconContainerProps> = ({
  children,
  size = 'md',
  bgColor = 'bg-gray-100',
  textColor = 'text-gray-400',
}) => {
  return (
    <div
      className={`flex items-center justify-center ${sizeClasses[size]} ${bgColor} ${textColor}`}
    >
      {children}
    </div>
  );
};
