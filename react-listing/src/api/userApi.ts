import { UsersResponse } from '../types/user';
import { API_BASE_URL, API_TIMEOUT } from '../utils/constants';

class ApiError extends Error {
  constructor(
    message: string,
    public status?: number
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

export const fetchUsers = async (): Promise<UsersResponse> => {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), API_TIMEOUT);

  try {
    const response = await fetch(`${API_BASE_URL}/users`, {
      signal: controller.signal,
      headers: {
        'Content-Type': 'application/json',
      },
    });

    clearTimeout(timeoutId);

    if (!response.ok) {
      throw new ApiError(
        `Failed to fetch users: ${response.statusText}`,
        response.status
      );
    }

    const data: UsersResponse = await response.json();

    if (!Array.isArray(data)) {
      throw new ApiError('Invalid response format: expected an array');
    }

    return data;
  } catch (error) {
    clearTimeout(timeoutId);

    if (error instanceof ApiError) {
      throw error;
    }

    if (error instanceof DOMException && error.name === 'AbortError') {
      throw new ApiError('Request timed out. Please try again.');
    }

    throw new ApiError('Network error. Please check your connection.');
  }
};
