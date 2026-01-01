import { ExternalLink } from 'lucide-react';

interface ContactInfoItemProps {
  icon: React.ReactNode;
  label: string;
  value: string;
  secondaryValue?: string;
  href?: string;
  external?: boolean;
  iconBgColor: string;
  iconTextColor: string;
}

export const ContactInfoItem: React.FC<ContactInfoItemProps> = ({
  icon,
  label,
  value,
  secondaryValue,
  href,
  external = false,
  iconBgColor,
  iconTextColor,
}) => {
  const content = (
    <>
      <div
        className={`flex h-10 w-10 items-center justify-center rounded-lg ${iconBgColor} ${iconTextColor}`}
      >
        {icon}
      </div>
      <div className="min-w-0 flex-1">
        <p className="text-xs font-medium text-gray-500">{label}</p>
        <p className="truncate text-sm font-medium text-gray-900">{value}</p>
        {secondaryValue && (
          <p className="text-sm text-gray-600">{secondaryValue}</p>
        )}
      </div>
      {external && <ExternalLink className="h-4 w-4 text-gray-400" />}
    </>
  );

  const baseClasses =
    'flex items-center gap-3 rounded-xl bg-gray-50 p-3 transition-colors';

  if (href) {
    return (
      <a
        href={href}
        target={external ? '_blank' : undefined}
        rel={external ? 'noopener noreferrer' : undefined}
        className={`${baseClasses} hover:bg-gray-100`}
      >
        {content}
      </a>
    );
  }

  return <div className={baseClasses}>{content}</div>;
};
