import { useState, useEffect, useCallback } from 'react';
import { UsersState } from '../types/user';
import { fetchUsers } from '../api/userApi';

interface UseUsersReturn extends UsersState {
  refetch: () => Promise<void>;
}

export const useUsers = (): UseUsersReturn => {
  const [state, setState] = useState<UsersState>({
    users: [],
    isLoading: true,
    error: null,
  });

  const fetchData = useCallback(async () => {
    setState((prev) => ({ ...prev, isLoading: true, error: null }));

    try {
      const data = await fetchUsers();
      setState({ users: data, isLoading: false, error: null });
    } catch (err) {
      const errorMessage =
        err instanceof Error ? err.message : 'An unexpected error occurred';
      setState({ users: [], isLoading: false, error: errorMessage });
    }
  }, []);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  return {
    ...state,
    refetch: fetchData,
  };
};
