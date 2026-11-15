const TOKEN_STORAGE_KEY = 'rg_admin_token';

export function getStoredAuthToken(): string | null {
  try {
    return localStorage.getItem(TOKEN_STORAGE_KEY);
  } catch {
    return null;
  }
}

export function setStoredAuthToken(token: string | null): void {
  try {
    if (token) {
      localStorage.setItem(TOKEN_STORAGE_KEY, token);
    } else {
      localStorage.removeItem(TOKEN_STORAGE_KEY);
    }
  } catch {
    // Ignore storage unavailability
  }
}

export const getAuthToken = getStoredAuthToken;
export const setAuthToken = setStoredAuthToken;

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status: number,
    public readonly code = 'api_error'
  ) {
    super(message);
  }
}

export async function apiRequest<T>(
  path: string,
  options: RequestInit & { auth?: boolean } = {}
): Promise<T> {
  const headers = new Headers(options.headers || {});
  if (!(options.body instanceof FormData) && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }
  const shouldAttachAuth =
    options.auth === true ||
    (options.auth !== false &&
      (path.startsWith('/api/v1/admin') || path.startsWith('/api/v1/auth/me') || path.startsWith('/api/v1/auth/logout')));
  if (shouldAttachAuth) {
    const token = getStoredAuthToken();
    if (token) {
      headers.set('Authorization', `Bearer ${token}`);
    }
  }

  const res = await fetch(path, {
    ...options,
    headers,
    credentials: 'include',
  });

  const payload = (await res.json().catch(() => ({}))) as {
    success?: boolean;
    data?: T;
    error?: { message?: string; code?: string };
    message?: string;
  };

  if (!res.ok || payload.success === false) {
    throw new ApiError(
      payload.error?.message || payload.message || `Request failed (${res.status})`,
      res.status,
      payload.error?.code || 'api_error'
    );
  }

  return (payload.data !== undefined ? payload.data : (payload as unknown as T));
}
