import crypto from 'node:crypto';

export function sha256Hex(value: string): string {
  return crypto.createHash('sha256').update(value, 'utf8').digest('hex');
}

export function hmacSha256Hex(payload: string | Buffer, secret: string): string {
  return crypto.createHmac('sha256', secret).update(payload).digest('hex');
}

export function timingSafeEqualStr(a: string, b: string): boolean {
  const bufA = Buffer.from(a, 'utf8');
  const bufB = Buffer.from(b, 'utf8');
  if (bufA.length !== bufB.length) return false;
  return crypto.timingSafeEqual(bufA, bufB);
}

export function randomTokenHex(bytes = 32): string {
  return crypto.randomBytes(bytes).toString('hex');
}

export function hashPassword(password: string): string {
  const salt = crypto.randomBytes(16).toString('hex');
  const derived = crypto.scryptSync(password, salt, 64).toString('hex');
  return `${salt}:${derived}`;
}

export function verifyPassword(password: string, storedHash: string): boolean {
  const [salt, expectedHex] = storedHash.split(':');
  if (!salt || !expectedHex) return false;
  const derived = crypto.scryptSync(password, salt, 64).toString('hex');
  return timingSafeEqualStr(derived, expectedHex);
}

export interface SessionJwtPayload {
  sub: string;
  email: string;
  role: string;
  iat: number;
  exp: number;
}

function base64UrlEncode(input: string | Buffer): string {
  return Buffer.from(input)
    .toString('base64')
    .replace(/=/g, '')
    .replace(/\+/g, '-')
    .replace(/\//g, '_');
}

function base64UrlDecode(input: string): string {
  const padded = input.replace(/-/g, '+').replace(/_/g, '/').padEnd(Math.ceil(input.length / 4) * 4, '=');
  return Buffer.from(padded, 'base64').toString('utf8');
}

export function signSessionJwt(
  payload: Omit<SessionJwtPayload, 'iat' | 'exp'>,
  secret: string,
  ttlSeconds = 60 * 60 * 12
): string {
  const now = Math.floor(Date.now() / 1000);
  const fullPayload: SessionJwtPayload = {
    ...payload,
    iat: now,
    exp: now + ttlSeconds,
  };
  const header = base64UrlEncode(JSON.stringify({ alg: 'HS256', typ: 'JWT' }));
  const body = base64UrlEncode(JSON.stringify(fullPayload));
  const sig = base64UrlEncode(
    crypto.createHmac('sha256', secret).update(`${header}.${body}`).digest()
  );
  return `${header}.${body}.${sig}`;
}

export function verifySessionJwt(token: string, secret: string): SessionJwtPayload | null {
  const parts = token.split('.');
  if (parts.length !== 3) return null;
  const [header, body, sig] = parts;
  const expectedSig = base64UrlEncode(
    crypto.createHmac('sha256', secret).update(`${header}.${body}`).digest()
  );
  if (!timingSafeEqualStr(sig, expectedSig)) return null;
  try {
    const parsed = JSON.parse(base64UrlDecode(body)) as SessionJwtPayload;
    if (!parsed.sub || !parsed.exp || parsed.exp < Math.floor(Date.now() / 1000)) {
      return null;
    }
    return parsed;
  } catch {
    return null;
  }
}
