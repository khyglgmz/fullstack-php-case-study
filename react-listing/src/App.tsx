import { useState, useMemo } from 'react';
import { useUsers } from './hooks/useUsers';
import { useDebounce } from './hooks/useDebounce';
import { useModal } from './hooks/useModal';
import { UserSearch } from './components/users/UserSearch';
import { UserList } from './components/users/UserList';
import { UserModal } from './components/users/UserModal';
import { Users } from 'lucide-react';
import { DEBOUNCE_DELAY } from './utils/constants';

function App() {
  const [searchTerm, setSearchTerm] = useState('');
  const debouncedSearch = useDebounce(searchTerm, DEBOUNCE_DELAY);
  const { users, isLoading, error, refetch } = useUsers();
  const { isOpen, selectedUser, openModal, closeModal } = useModal();

  const isSearching = searchTerm !== debouncedSearch;

  const filteredUsers = useMemo(() => {
    if (!debouncedSearch.trim()) return users;

    const searchLower = debouncedSearch.toLowerCase();
    return users.filter((user) =>
      user.name.toLowerCase().includes(searchLower)
    );
  }, [users, debouncedSearch]);

  const resultCount = filteredUsers.length;
  const hasSearch = debouncedSearch.trim().length > 0;

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
      <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <header className="mb-8">
          <div className="mb-6 flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-200">
              <Users className="h-5 w-5 text-white" />
            </div>
            <div>
              <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                User List
              </h1>
              <p className="text-sm text-gray-500">
                {users.length} users total
              </p>
            </div>
          </div>

          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <UserSearch
              value={searchTerm}
              onChange={setSearchTerm}
              placeholder="Search users by name..."
              isSearching={isSearching}
            />
            {hasSearch && !isLoading && (
              <p className="text-sm text-gray-500">
                {resultCount === 0 ? (
                  'No matches'
                ) : (
                  <>
                    <span className="font-medium text-indigo-600">{resultCount}</span>
                    {resultCount === 1 ? ' match' : ' matches'} found
                  </>
                )}
              </p>
            )}
          </div>
        </header>

        <main>
          <UserList
            users={filteredUsers}
            isLoading={isLoading}
            error={error}
            onUserClick={openModal}
            onRetry={refetch}
          />
        </main>

        <UserModal user={selectedUser} isOpen={isOpen} onClose={closeModal} />
      </div>
    </div>
  );
}

export default App;
