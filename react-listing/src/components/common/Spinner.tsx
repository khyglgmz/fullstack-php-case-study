export const Spinner: React.FC = () => {
  return (
    <div className="flex flex-col items-center justify-center py-20" role="status">
      <div className="relative">
        <div className="h-12 w-12 rounded-full border-4 border-indigo-100"></div>
        <div className="absolute left-0 top-0 h-12 w-12 animate-spin rounded-full border-4 border-transparent border-t-indigo-600"></div>
      </div>
      <p className="mt-4 text-sm font-medium text-gray-500">Loading users...</p>
      <span className="sr-only">Loading...</span>
    </div>
  );
};
